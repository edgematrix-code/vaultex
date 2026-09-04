<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Render the authenticated dashboard.
     */
    public function __invoke(Request $request, WalletService $wallets, TransactionService $transactions): Response
    {
        $user = $request->user();

        $wallets->ensureProvisioned($user);

        return Inertia::render('Dashboard', [
            'balances' => $wallets->overview($user),
            'transactions' => $transactions->list($user),
            'history' => $wallets->history($user),
            'security' => $wallets->security($user),
        ]);
    }
}
