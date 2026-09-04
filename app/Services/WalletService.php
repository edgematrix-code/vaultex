<?php

namespace App\Services;

use App\Models\PortfolioSnapshot;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns the read model powering the wallet dashboard pages.
 *
 * A brand new account starts without a real blockchain connection, so every
 * user is provisioned with a set of demo wallets (per supported chain),
 * sample activity and a portfolio history the moment their account exists.
 */
class WalletService
{
    /**
     * Chains that share a single EVM address (ERC-20 assets on Ethereum).
     */
    private const EVM_SHARED_CHAINS = ['usdt', 'usdc'];

    /**
     * Notification events exposed in the settings UI.
     */
    public const PREFERENCE_EVENTS = ['deposit_received', 'withdrawal_sent', 'new_device_login', 'price_alerts'];

    /**
     * Default notification toggles for a new account.
     */
    private const PREFERENCE_DEFAULTS = [
        'deposit_received' => ['email' => true, 'in_app' => true],
        'withdrawal_sent' => ['email' => true, 'in_app' => true],
        'new_device_login' => ['email' => true, 'in_app' => true],
        'price_alerts' => ['email' => false, 'in_app' => true],
    ];

    public function __construct(private readonly CoinGecko $prices)
    {
        //
    }

    /**
     * Create the full demo wallet state for a freshly registered user.
     */
    public function provision(User $user): void
    {
        if ($user->wallets()->exists()) {
            return;
        }

        DB::transaction(function () use ($user) {
            $this->createWallets($user);
            $this->createSampleTransactions($user);
            $this->createPortfolioHistory($user);
            $this->createNotificationPreferences($user);
        });
    }

    /**
     * Provision the user only when they have no wallet rows yet.
     */
    public function ensureProvisioned(User $user): bool
    {
        if ($user->wallets()->exists()) {
            return false;
        }

        $this->provision($user);

        return true;
    }

    /**
     * Serialize the per-chain balances for a user.
     *
     * @return array<int, array{chain: string, address: string, balance: float, usdValue: float, priceUsd: float, change24hPct: float}>
     */
    public function overview(User $user): array
    {
        $this->ensureProvisioned($user);

        $wallets = $user->wallets()->get()->keyBy('chain');
        $live = $this->prices->prices();
        $rows = [];

        foreach (config('wallet.chains') as $chain => $config) {
            /** @var Wallet|null $wallet */
            $wallet = $wallets->get($chain);

            if (! $wallet) {
                continue;
            }

            // Prefer a fresh live quote; otherwise fall back to the last
            // stored price so pages never break when the API is down.
            $quote = $live[$chain] ?? null;
            $priceUsd = $quote['priceUsd'] ?? $wallet->price_usd;
            $change24hPct = $quote['change24hPct'] ?? $wallet->change_24h_pct;

            if ($quote !== null && ($wallet->price_usd !== $priceUsd || $wallet->change_24h_pct !== $change24hPct)) {
                $wallet->forceFill([
                    'price_usd' => $priceUsd,
                    'change_24h_pct' => $change24hPct,
                ])->save();
            }

            $rows[] = [
                'chain' => $chain,
                'address' => $wallet->address,
                'balance' => (float) $wallet->balance,
                'usdValue' => round((float) $wallet->balance * $priceUsd, 2),
                'priceUsd' => $priceUsd,
                'change24hPct' => $change24hPct,
            ];
        }

        return $rows;
    }

    /**
     * Serialize the daily portfolio history used by the dashboard chart.
     *
     * @return array<int, array{label: string, value: float}>
     */
    public function history(User $user, int $days = 30): array
    {
        $this->ensureProvisioned($user);

        $rows = PortfolioSnapshot::query()
            ->where('user_id', $user->id)
            ->where('snapshot_date', '>=', Carbon::today()->subDays($days))
            ->orderBy('snapshot_date')
            ->get();

        return $rows
            ->map(fn (PortfolioSnapshot $snapshot) => [
                'label' => $snapshot->snapshot_date->format('M j'),
                'value' => (float) $snapshot->value_usd,
            ])
            ->all();
    }

    /**
     * Serialize the security summary shown on the dashboard.
     *
     * @return array{twoFactorEnabled: bool, lastLogin: string, lastLoginDevice: string, nonCustodial: true}
     */
    public function security(User $user): array
    {
        return [
            'twoFactorEnabled' => $user->two_factor_confirmed_at !== null,
            'lastLogin' => $user->last_login_at?->toIso8601String() ?? '',
            'lastLoginDevice' => $user->last_login_at
                ? sprintf('%s · %s', $this->describeDevice($user->last_login_user_agent), $user->last_login_ip ?? 'unknown IP')
                : 'No recent sign-ins recorded',
            'nonCustodial' => true,
        ];
    }

    /**
     * Create one wallet row per supported chain with a generated deposit
     * address and the demo starting balance.
     */
    private function createWallets(User $user): void
    {
        $ethAddress = $this->evmAddress();

        foreach (config('wallet.chains') as $chain => $config) {
            $address = match (true) {
                $chain === 'btc' => $this->bitcoinAddress(),
                $chain === 'trx' => $this->tronAddress(),
                $chain === 'bsc' => $this->evmAddress(),
                in_array($chain, self::EVM_SHARED_CHAINS, true) => $ethAddress,
                default => $ethAddress,
            };

            Wallet::create([
                'user_id' => $user->id,
                'chain' => $chain,
                'address' => $address,
                'balance' => $config['initial_balance'],
                'price_usd' => $config['seed_price'],
                'change_24h_pct' => $config['seed_change'],
            ]);
        }
    }

    /**
     * Seed a handful of transactions so the activity feeds feel alive.
     */
    private function createSampleTransactions(User $user): void
    {
        $addresses = $user->wallets()->pluck('address', 'chain');
        $now = Carbon::now();
        $seed = fn (string $chain): float => (float) config("wallet.chains.{$chain}.seed_price", 0);

        $samples = [
            [
                'chain' => 'btc', 'type' => 'deposit', 'status' => 'completed',
                'amount' => 0.0842, 'fee' => 0.0, 'createdAt' => $now->copy()->subDays(2)->setTime(14, 32),
                'from' => $this->bitcoinAddress(), 'to' => $addresses['btc'] ?? null,
            ],
            [
                'chain' => 'eth', 'type' => 'withdrawal', 'status' => 'completed',
                'amount' => 1.2, 'fee' => 2.10, 'createdAt' => $now->copy()->subDays(4)->setTime(9, 14),
                'from' => $addresses['eth'] ?? null, 'to' => $this->evmAddress(),
            ],
            [
                'chain' => 'usdt', 'type' => 'withdrawal', 'status' => 'pending',
                'amount' => 2500.0, 'fee' => 4.20, 'createdAt' => $now->copy()->subDays(5)->setTime(20, 2),
                'from' => $addresses['usdt'] ?? null, 'to' => $this->evmAddress(),
            ],
            [
                'chain' => 'bsc', 'type' => 'deposit', 'status' => 'completed',
                'amount' => 6.0, 'fee' => 0.0, 'createdAt' => $now->copy()->subDays(7)->setTime(11, 50),
                'from' => $this->evmAddress(), 'to' => $addresses['bsc'] ?? null,
            ],
            [
                'chain' => 'trx', 'type' => 'deposit', 'status' => 'failed',
                'amount' => 1200.0, 'fee' => 0.0, 'createdAt' => $now->copy()->subDays(10)->setTime(3, 21),
                'from' => $this->tronAddress(), 'to' => $addresses['trx'] ?? null,
                'note' => 'Below minimum confirmation threshold — funds returned to sender.',
            ],
            [
                'chain' => 'usdc', 'type' => 'internal', 'status' => 'completed',
                'amount' => 900.0, 'fee' => 0.0, 'createdAt' => $now->copy()->subDays(14)->setTime(16, 40),
                'from' => $addresses['usdc'] ?? null, 'to' => $addresses['usdc'] ?? null,
            ],
        ];

        foreach ($samples as $sample) {
            Transaction::create([
                'user_id' => $user->id,
                'chain' => $sample['chain'],
                'type' => $sample['type'],
                'status' => $sample['status'],
                'amount' => $sample['amount'],
                'usd_value' => round($sample['amount'] * $seed($sample['chain']), 2),
                'fee_usd' => $sample['fee'],
                'tx_hash' => $this->fakeTxHash($sample['chain']),
                'from_address' => $sample['from'],
                'to_address' => $sample['to'],
                'note' => $sample['note'] ?? null,
                'confirmed_at' => $sample['status'] === 'completed' ? $sample['createdAt']->copy()->addMinutes(15) : null,
                'created_at' => $sample['createdAt'],
                'updated_at' => $sample['createdAt'],
            ]);
        }
    }

    /**
     * Generate a smooth ~30 day portfolio curve ending at the demo total.
     */
    private function createPortfolioHistory(User $user): void
    {
        $days = 30;
        $seed = 42;
        $value = 68400.0;
        $today = Carbon::today();

        for ($i = $days; $i >= 0; $i--) {
            $seed = ($seed * 9301 + 49297) % 233280;
            $drift = ($seed / 233280) - 0.47;

            $value = max($value + $drift * $value * 0.018, 1000.0);

            PortfolioSnapshot::create([
                'user_id' => $user->id,
                'snapshot_date' => $today->copy()->subDays($i),
                'value_usd' => round($value, 2),
            ]);
        }
    }

    /**
     * Serialize the user's notification preference toggles, seeding the
     * defaults the first time they are requested.
     *
     * @return array<int, array{event: string, email: bool, inApp: bool}>
     */
    public function notificationPreferences(User $user): array
    {
        $rows = $user->notificationPreferences()->get();

        if ($rows->isEmpty()) {
            $this->createNotificationPreferences($user);
            $rows = $user->notificationPreferences()->get();
        }

        return $rows
            ->map(fn ($preference) => [
                'event' => $preference->event,
                'email' => (bool) $preference->email,
                'inApp' => (bool) $preference->in_app,
            ])
            ->values()
            ->all();
    }

    /**
     * Persist the notification toggles submitted from the settings page.
     *
     * @param  array<int, array{event: string, email: bool, inApp: bool}>  $preferences
     */
    public function updateNotificationPreferences(User $user, array $preferences): void
    {
        foreach ($preferences as $preference) {
            $user->notificationPreferences()->updateOrCreate(
                ['event' => $preference['event']],
                [
                    'email' => $preference['email'],
                    'in_app' => $preference['inApp'],
                ],
            );
        }
    }

    /**
     * Seed the default notification toggles shown in settings.
     */
    private function createNotificationPreferences(User $user): void
    {
        foreach (self::PREFERENCE_DEFAULTS as $event => $defaults) {
            $user->notificationPreferences()->create([
                'event' => $event,
                'email' => $defaults['email'],
                'in_app' => $defaults['in_app'],
            ]);
        }
    }

    /**
     * Describe a browser + OS from a user agent, e.g. "Chrome on Windows".
     */
    private function describeDevice(?string $userAgent): string
    {
        $agent = $userAgent ?? '';

        $browser = match (true) {
            Str::contains($agent, ['Edg/']) => 'Edge',
            Str::contains($agent, ['Chrome/', 'CriOS/']) => 'Chrome',
            Str::contains($agent, ['Firefox/', 'FxiOS/']) => 'Firefox',
            Str::contains($agent, ['Safari/']) => 'Safari',
            default => 'Unknown browser',
        };

        $os = match (true) {
            Str::contains($agent, ['Windows']) => 'Windows',
            Str::contains($agent, ['Android']) => 'Android',
            Str::contains($agent, ['iPhone', 'iPad']) => 'iOS',
            Str::contains($agent, ['Mac OS X', 'Macintosh']) => 'macOS',
            Str::contains($agent, ['Linux']) => 'Linux',
            default => 'Unknown OS',
        };

        return "{$browser} on {$os}";
    }

    private function evmAddress(): string
    {
        return '0x'.Str::lower(bin2hex(random_bytes(20)));
    }

    private function bitcoinAddress(): string
    {
        return 'bc1q'.$this->randomChars(38, '023456789acdefghjklmnpqrstuvwxyz');
    }

    private function tronAddress(): string
    {
        return 'T'.$this->randomChars(33, '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz');
    }

    private function randomChars(int $length, string $alphabet): string
    {
        $result = '';
        $max = Str::length($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= Str::substr($alphabet, random_int(0, $max), 1);
        }

        return $result;
    }

    private function fakeTxHash(string $chain): string
    {
        $hex = Str::lower(bin2hex(random_bytes(32)));

        return in_array($chain, ['btc', 'trx'], true) ? $hex : '0x'.$hex;
    }
}
