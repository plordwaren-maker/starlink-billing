<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        Bill::generateDueBills();

        $billQuery = Bill::with('client')
            ->whereHas('client')
            // Hide duplicate history rows for the same named client and billing start date.
            // Keep the original (lowest-ID) row; distinct billing periods remain visible.
            ->whereNotExists(function ($duplicate) {
                $duplicate->selectRaw('1')
                    ->from('bills as duplicate_bills')
                    ->join('clients as duplicate_clients', 'duplicate_clients.id', '=', 'duplicate_bills.client_id')
                    ->join('clients as history_clients', 'history_clients.id', '=', 'bills.client_id')
                    ->whereRaw('LOWER(TRIM(duplicate_clients.name)) = LOWER(TRIM(history_clients.name))')
                    ->whereRaw('DATE(duplicate_bills.period_start) = DATE(bills.period_start)')
                    ->whereColumn('duplicate_bills.id', '<', 'bills.id');
            })
            // If a client still has an unpaid cycle due today or earlier, hide
            // the automatically generated future cycle from this follow-up view.
            // This keeps the due/overdue (green-today) entry instead of showing
            // a second upcoming row for the same client.
            ->whereNotExists(function ($currentDue) {
                $currentDue->selectRaw('1')
                    ->from('bills as current_due_bills')
                    ->whereColumn('current_due_bills.client_id', 'bills.client_id')
                    ->whereColumn('current_due_bills.id', '<>', 'bills.id')
                    ->whereColumn('current_due_bills.due_at', '<', 'bills.due_at')
                    ->whereRaw('(current_due_bills.amount - current_due_bills.paid_amount) > 0.005')
                    ->whereDate('current_due_bills.due_at', '<=', now()->toDateString());
            })
            ->orderByRaw(
                "CASE
                    WHEN (amount - paid_amount) > 0.005 AND DATE(due_at) = ? THEN 0
                    WHEN (amount - paid_amount) > 0.005 AND DATE(due_at) < ? THEN 1
                    ELSE 2
                END ASC",
                [now()->toDateString(), now()->toDateString()]
            )
            ->orderBy('due_at', 'asc')
            ->orderBy('id', 'asc');
        $paymentQuery = PaymentTransaction::with(['client', 'bill.client'])
            ->whereHas('client')->whereHas('bill')->latest('received_at');

        if ($request->filled('client_id')) {
            $billQuery->where('client_id', $request->integer('client_id'));
            $paymentQuery->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('month')) {
            $year = (int) substr($request->month, 0, 4);
            $month = (int) substr($request->month, 5, 2);
            $billQuery->whereYear('due_at', $year)->whereMonth('due_at', $month);
            $paymentQuery->whereYear('received_at', $year)->whereMonth('received_at', $month);
        }

        $bills = $billQuery->paginate(20, ['*'], 'bills_page')->withQueryString();
        $transactions = $paymentQuery->paginate(20, ['*'], 'payments_page')->withQueryString();
        $clients = Client::orderBy('name')->get();

        return view('history.index', compact('bills', 'transactions', 'clients'));
    }
}
