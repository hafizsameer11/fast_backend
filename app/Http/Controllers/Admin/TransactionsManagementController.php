<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionsManagementController extends Controller
{
    public function getTransactions()
    {
        $transactions = Transaction::with('user')->orderBy('created_at', 'desc')->get();
        $totalTransactions = Transaction::count();
        $completedTransactions = Transaction::where('status', 'completed')->count();
        $pendingTransactions = Transaction::where('status', 'pending')->count();
        $failedTransactions = Transaction::where('status', 'failed')->count();
        $data = [
            'totalTransactions' => $totalTransactions,
            'completedTransactions' => $completedTransactions,
            'pendingTransactions' => $pendingTransactions,
            'failedTransactions' => $failedTransactions,
            'transactions' => $transactions,
        ];
        return response()->json($data, 200);
    }

}
