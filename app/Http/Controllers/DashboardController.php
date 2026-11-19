<?php

namespace App\Http\Controllers;

use App\Models\{Bill, Client, PaymentTransaction, ActivityLog, AppSetting, BillingNotification};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        Bill::generateDueBills();
        $now = now();
        Bill::with('client')->whereHas('client', fn($q) => $q->where('active', true))
            ->whereDate('due_at', '<=', $now->toDateString())
            ->whereRaw('amount > paid_amount')->get()->each(function ($bill) {
                $message = 'Payment due: '.$bill->client->name.' has an unpaid Starlink bill of ₱'.number_format($bill->balance, 2).' (due '.$bill->due_at->format('M d, Y g:i A').').';
                BillingNotification::firstOrCreate(['message' => $message], ['is_read' => false]);
            });
        $notifications = BillingNotification::latest()->take(12)->get();
        $unreadNotificationCount = BillingNotification::where('is_read', false)->count();
        $openBills = Bill::with('client')->whereHas('client', fn($q) => $q->where('active', true))
            ->whereRaw('amount > paid_amount')->orderBy('due_at')->get();
        $collected = PaymentTransaction::whereMonth('received_at', $now->month)
            ->whereYear('received_at', $now->year)->sum('amount');
        $overdue = $openBills->filter(fn($b) => $b->due_at->isPast());
        $dueSoon = $openBills->filter(fn($b) => $b->due_at->between(now(), now()->copy()->addDays((int) (AppSetting::current()->reminder_days ?? 3))));
        $monthBills = Bill::whereYear('due_at', $now->year)->whereMonth('due_at', $now->month)->get();
        $billsDueThisMonth = $monthBills->count();
        $paidBillsThisMonth = $monthBills->filter(fn($b) => $b->balance <= 0.005)->count();
        $clients = Client::with('bills')->orderBy('name')->get();
        // Count actual active client records, not billing cycles or payment rows.
        $activeClientsCount = Client::where('active', true)->count();
        // Keep one relevant billing cycle visible for every active subscription. A due/overdue
        // unpaid cycle takes priority over a newly generated next-month cycle.
        $followUpBills = $clients->where('active', true)
            ->map(fn ($client) => $client->currentFollowUpBill())
            ->filter()
            // Due-today unpaid subscriptions must always be the first rows.
            // Then show overdue, upcoming unpaid, and finally paid subscriptions.
            ->sort(function ($a, $b) use ($now) {
                $priority = function ($bill) use ($now) {
                    if ($bill->balance > 0.005 && $bill->due_at?->toDateString() === $now->toDateString()) return 0;
                    if ($bill->balance > 0.005 && $bill->due_at?->lt($now)) return 1;
                    if ($bill->balance > 0.005) return 2;
                    return 3;
                };
                $priorityCompare = $priority($a) <=> $priority($b);
                if ($priorityCompare !== 0) return $priorityCompare;
                return ($a->due_at?->timestamp ?? PHP_INT_MAX) <=> ($b->due_at?->timestamp ?? PHP_INT_MAX);
            })
            ->values();
        // Outstanding is the remaining balance on each active client's latest
        // subscription cycle, not the sum of every unpaid historical cycle.
        $currentOutstanding = $followUpBills->sum(fn ($bill) => $bill->balance);
        $recentPayments = PaymentTransaction::with(['client', 'bill'])->whereHas('client')->whereHas('bill')->latest('received_at')->take(6)->get();
        $activity = ActivityLog::latest()->take(8)->get();
        $settings = AppSetting::current();
        $chartData = collect(range(5, 0))->map(function ($offset) use ($now) {
            $month = $now->copy()->startOfMonth()->subMonths($offset);
            $bills = Bill::whereYear('due_at', $month->year)->whereMonth('due_at', $month->month)->get();
            $collected = PaymentTransaction::whereYear('received_at', $month->year)
                ->whereMonth('received_at', $month->month)->sum('amount');
            return ['label' => $month->format('M'), 'billed' => (float) $bills->sum('amount'), 'paid' => (float) $collected];
        })->all();

        return view('dashboard', compact('openBills', 'collected', 'overdue', 'dueSoon', 'clients',
            'recentPayments', 'activity', 'settings', 'chartData', 'paidBillsThisMonth', 'billsDueThisMonth', 'followUpBills', 'currentOutstanding', 'notifications', 'unreadNotificationCount', 'activeClientsCount'));
    }
}
