<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

/**
 * Persists and serializes wallet activity.
 *
 * Withdrawals go through a light simulation of a real broadcast: the funds
 * are reserved on the wallet, a transaction is stored as pending, and when
 * the user has two-factor authentication enabled the submitted code is
 * verified against their Fortify TOTP secret before anything is recorded.
 */
class TransactionService
{
    public function __construct(private readonly CoinGecko $prices)
    {
        //
    }

    /**
     * Serialize every transaction belonging to the user, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function list(User $user): array
    {
        return $user->transactions()->get()
            ->map(fn (Transaction $transaction) => $this->serialize($transaction))
            ->values()
            ->all();
    }

    /**
     * Find a transaction owned by the user.
     */
    public function find(User $user, int $id): ?Transaction
    {
        return Transaction::query()
            ->where('user_id', $user->id)
            ->find($id);
    }

    /**
     * Validate and persist a withdrawal request.
     *
     * @return array{ok: true, transaction: Transaction}|array{ok: false, field: string, error: string}
     */
    public function withdraw(User $user, string $chain, string $destination, float $amount, ?string $otp = null): array
    {
        $config = config("wallet.chains.{$chain}");

        /** @var Wallet|null $wallet */
        $wallet = $user->wallets()->where('chain', $chain)->first();

        if ($config === null || $wallet === null) {
            return ['ok' => false, 'field' => 'chain', 'error' => 'That asset is not supported.'];
        }

        if ($amount <= 0) {
            return ['ok' => false, 'field' => 'amount', 'error' => 'Enter an amount greater than zero.'];
        }

        if ($amount > (float) $wallet->balance) {
            return ['ok' => false, 'field' => 'amount', 'error' => 'Insufficient balance for this transfer.'];
        }

        // Two-factor authentication is required to release funds when enabled.
        if ($user->two_factor_confirmed_at !== null && $user->two_factor_secret !== null) {
            if ($otp === null || ! preg_match('/^\d{6}$/', $otp)) {
                return ['ok' => false, 'field' => 'otp', 'error' => 'Enter the 6-digit code from your authenticator app.'];
            }

            $valid = app(TwoFactorAuthenticationProvider::class)
                ->verify(decrypt($user->two_factor_secret), $otp);

            if (! $valid) {
                return ['ok' => false, 'field' => 'otp', 'error' => 'The two-factor code you entered is invalid.'];
            }
        }

        $priceUsd = $this->priceFor($wallet);
        $amountUsd = round($amount * $priceUsd, 2);
        $feeUsd = max((float) config('wallet.network_fee_min_usd'), round($amountUsd * (float) config('wallet.network_fee_rate'), 2));

        $transaction = DB::transaction(function () use ($user, $wallet, $chain, $destination, $amount, $amountUsd, $feeUsd) {
            $wallet->forceFill([
                'balance' => max((float) $wallet->balance - $amount, 0),
            ])->save();

            return Transaction::create([
                'user_id' => $user->id,
                'chain' => $chain,
                'type' => 'withdrawal',
                'status' => 'pending',
                'amount' => $amount,
                'usd_value' => $amountUsd,
                'fee_usd' => $feeUsd,
                'tx_hash' => $this->fakeTxHash($chain),
                'from_address' => $wallet->address,
                'to_address' => $destination,
                'note' => 'Awaiting network confirmation.',
            ]);
        });

        return ['ok' => true, 'transaction' => $transaction];
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'chain' => $transaction->chain,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'amount' => (float) $transaction->amount,
            'usdValue' => (float) $transaction->usd_value,
            'fee' => (float) $transaction->fee_usd,
            'txHash' => $transaction->tx_hash ?? '',
            'fromAddress' => $transaction->from_address ?? '',
            'toAddress' => $transaction->to_address ?? '',
            'createdAt' => $transaction->created_at?->toIso8601String() ?? '',
            'confirmedAt' => $transaction->confirmed_at?->toIso8601String(),
            'note' => $transaction->note,
        ];
    }

    private function priceFor(Wallet $wallet): float
    {
        $live = $this->prices->prices();

        return (float) ($live[$wallet->chain]['priceUsd'] ?? $wallet->price_usd);
    }

    private function fakeTxHash(string $chain): string
    {
        $hex = strtolower(bin2hex(random_bytes(32)));

        return in_array($chain, ['btc', 'trx'], true) ? $hex : '0x'.$hex;
    }
}
