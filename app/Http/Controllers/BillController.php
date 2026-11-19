<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Client;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request) {
        Bill::generateDueBills();
        $query = Bill::with('client')->whereHas('client')->latest('due_at');
        if ($request->filled('client_id')) $query->where('client_id', $request->integer('client_id'));
        if ($request->filled('month')) $query->whereYear('due_at', substr($request->month,0,4))->whereMonth('due_at', substr($request->month,5,2));
        $bills = $query->paginate(25)->withQueryString();
        $clients = Client::orderBy('name')->get();
        return view('payments.index', compact('bills', 'clients'));
    }

    public function create() {
        $clients = Client::where('active', true)->orderBy('name')->get();
        return view('bills.form', ['bill' => new Bill(['period_start' => now(), 'due_at' => now()->addDays(30)]), 'clients' => $clients]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id', 'period_start' => 'required|date',
            'due_at' => 'required|date|after_or_equal:period_start', 'amount' => 'required|numeric|min:0',
        ]);
        $bill = Bill::create($data + ['paid_amount' => 0, 'is_manual' => true, 'cycle_number' => null]);
        ActivityLog::create(['message' => 'Added manual bill for '.$bill->client->name, 'kind' => 'other']);
        return redirect()->route('bills.index')->with('success', 'Bill added.');
    }

    public function edit(Bill $bill) {
        $clients = Client::orderBy('name')->get();
        return view('bills.form', compact('bill', 'clients'));
    }

    public function update(Request $request, Bill $bill) {
        $data = $request->validate([
            'period_start' => 'required|date', 'due_at' => 'required|date|after_or_equal:period_start',
            'amount' => 'required|numeric|min:0',
        ]);
        $bill->update($data);
        return redirect()->route('bills.index')->with('success', 'Bill updated.');
    }

    public function destroy(Bill $bill) {
        if ($bill->cycle_number !== null) {
            $bill->client->skippedCycles()->firstOrCreate(['cycle_number' => $bill->cycle_number]);
        }
        $bill->delete();
        return back()->with('success', 'Bill and related transactions deleted.');
    }
}
