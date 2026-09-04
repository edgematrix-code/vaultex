<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    /**
     * The wallet overview page.
     */
    public function overview(Request $request, WalletService $wallets): Response
    {
        $user = $request->user();

        return Inertia::render('wallet/Overview', [
            'balances' => $wallets->overview($user),
        ]);
    }

    /**
     * The deposit flow start page.
     */
    public function deposit(): Response
    {
        return Inertia::render('wallet/Deposit');
    }

    /**
     * The withdrawal form page.
     */
    public function withdraw(Request $request, WalletService $wallets): Response
    {
        $user = $request->user();

        return Inertia::render('wallet/Withdraw', [
            'balances' => $wallets->overview($user),
        ]);
    }

    /**
     * The receive address page.
     */
    public function receive(Request $request, WalletService $wallets): Response
    {
        $user = $request->user();

        return Inertia::render('wallet/Receive', [
            'balances' => $wallets->overview($user),
        ]);
    }

    /**
     * Submit a withdrawal request.
     */
    public function store(Request $request, TransactionService $transactions): RedirectResponse
    {
        $validated = $request->validate([
            'chain' => ['required', Rule::in(array_keys(config('wallet.chains')))],
            'destination' => ['required', 'string', 'min:10', 'max:128', 'regex:/^[A-Za-z0-9]+$/'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'otp' => ['nullable', 'digits:6'],
        ]);

        $result = $transactions->withdraw(
            $request->user(),
            $validated['chain'],
            $validated['destination'],
            (float) $validated['amount'],
            $validated['otp'] ?? null,
        );

        if (! $result['ok']) {
            return redirect()
                ->back()
                ->withErrors([$result['field'] => $result['error']]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => sprintf(
                'Withdrawal of %s %s submitted and awaiting confirmation.',
                rtrim(rtrim(number_format($validated['amount'], 8, '.', ''), '0'), '.'),
                strtoupper($validated['chain']),
            ),
        ]);

        return redirect()->route('transactions.show', $result['transaction']);
    }
}
