<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index() {
        Bill::generateDueBills();
        $clients = Client::orderBy('name')->get();
        $bills = Bill::with('client')->whereHas('client')->latest('due_at')->get();
        return view('messages.index', compact('clients','bills'));
    }

    public function compose(Request $request) {
        Bill::generateDueBills();
        $client = Client::findOrFail($request->integer('client_id'));
        // Only allow a bill that belongs to the selected client. The bill stores
        // the share amount and exact cycle dates that were registered for that period.
        $bill = Bill::with('client')
            ->where('client_id', $client->id)
            ->findOrFail($request->integer('bill_id'));
        $settings = AppSetting::current();
        $type = $request->input('type', 'reminder');
        $date = now()->format('F j, Y');
        $formatPeriodDate = fn ($value) => $value->format('F j, Y (g:i A)');
        $period = $formatPeriodDate($bill->period_start).' – '.$formatPeriodDate($bill->due_at);
        $balance = $bill->balance;
        if ($type === 'receipt') {
            $message = "STARLINK INTERNET PAYMENT RECEIPT\n\nDate: {$date}\n\nTo: {$client->name}\n\nPayment received for your monthly share of the Starlink Internet Subscription, {$period} billing period.\n\nAmount Paid: ₱".number_format($bill->paid_amount,2)."\n\nBilling Period: {$period}\n\nRemaining Balance: ₱".number_format($balance,2)."\n\nReceived by: {$settings->receiver_name}\n\n---\n\nThank you for your payment!";
        } else {
            // Amount due is based on this client's selected bill, not the global
            // default share. For partial payments, show only the remaining balance.
            $due = $balance;
            $reason = trim((string) $settings->increase_reason);
            if ($reason === '') {
                $reason = 'Please be informed that the monthly internet share has increased from ₱1,000 to ₱1,150 due to an adjustment in the overall internet subscription and service expenses. This adjustment helps cover the cost of maintaining the Starlink internet service.';
            }
            $message = "STARLINK INTERNET PAYMENT NOTICE\n\nDate: {$date}\n\nTo: {$client->name}\n\nThis is a reminder for your monthly share for the Starlink Internet Subscription covering the {$period} billing period.\n\nAmount Due: ₱".number_format($due,2)."\n\nBilling Period: {$period}\n\nPayment Method: GCash ({$settings->gcash_number} – {$settings->receiver_name})\n\nReason for Billing Increase:\n\n{$reason}\n\nPurpose: Monthly Share – Starlink Internet Subscription ({$period})\n\nKindly send your payment at your earliest convenience.\n\nReceived by: {$settings->receiver_name}\n\n---\n\nThank you for your understanding, cooperation, and continued support!";
        }
        return response()->json(['message' => $message]);
    }
}
