<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    /**
     * The transaction history page.
     */
    public function index(Request $request, TransactionService $transactions): Response
    {
        return Inertia::render('transactions/Index', [
            'transactions' => $transactions->list($request->user()),
        ]);
    }

    /**
     * A single transaction detail page.
     */
    public function show(Request $request, TransactionService $transactions, Transaction $transaction): Response
    {
        abort_unless($transaction->user_id === $request->user()->id, 404);

        return Inertia::render('transactions/Show', [
            'transaction' => $transactions->serialize($transaction),
        ]);
    }
}
