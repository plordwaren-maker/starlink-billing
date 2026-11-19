<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\PaymentTransaction;
use App\Models\BillingNotification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        Bill::generateDueBills();

        // Eager-load nullable relations so old/orphaned records cannot crash the page.
        $transactions = PaymentTransaction::with(['client', 'bill.client'])
            ->latest('received_at')
            ->paginate(25);

        $clients = Client::orderBy('name')->get();

        // Only bills with a real client can be selected for a new payment.
        $bills = Bill::with('client')
            ->whereHas('client')
            ->whereRaw('amount > paid_amount')
            ->orderBy('due_at')
            ->get();

        return view('payments.transactions', compact('transactions', 'clients', 'bills'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|gt:0',
            'received_at' => 'required|date',
            'method' => 'required|in:GCash,Cash,Bank transfer,Other',
            'reference_number' => 'nullable|string|max:120',
        ]);

        DB::transaction(function () use ($data) {
            $bill = Bill::with('client')->lockForUpdate()->findOrFail($data['bill_id']);

            if (!$bill->client) {
                abort(422, 'This bill is not linked to an existing client. Please repair or delete the bill first.');
            }

            $tx = PaymentTransaction::create($data + ['client_id' => $bill->client_id]);
            $bill->increment('paid_amount', $tx->amount);

            BillingNotification::create([
                'message' => $bill->client->name.' paid ₱'.number_format($tx->amount, 2).' for their Starlink bill.'
            ]);
            ActivityLog::create([
                'message' => $bill->client->name.' payment recorded: ₱'.number_format($tx->amount, 2),
                'kind' => 'payment'
            ]);
        });

        return redirect()->route('payments.index')->with('success', 'Payment recorded.');
    }

    public function edit(PaymentTransaction $payment)
    {
        $payment->load(['client', 'bill.client']);
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, PaymentTransaction $payment)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|gt:0',
            'received_at' => 'required|date',
            'method' => 'required|in:GCash,Cash,Bank transfer,Other',
            'reference_number' => 'nullable|string|max:120',
        ]);

        DB::transaction(function () use ($payment, $data) {
            $bill = Bill::lockForUpdate()->findOrFail($payment->bill_id);
            $bill->paid_amount = max(0, (float) $bill->paid_amount - (float) $payment->amount + (float) $data['amount']);
            $bill->save();
            $payment->update($data);
        });

        return redirect()->route('payments.index')->with('success', 'Payment updated.');
    }

    public function destroy(PaymentTransaction $payment)
    {
        DB::transaction(function () use ($payment) {
            $bill = Bill::lockForUpdate()->find($payment->bill_id);
            if ($bill) {
                $bill->paid_amount = max(0, (float) $bill->paid_amount - (float) $payment->amount);
                $bill->save();
            }
            $payment->delete();
        });

        return back()->with('success', 'Payment deleted.');
    }
}
