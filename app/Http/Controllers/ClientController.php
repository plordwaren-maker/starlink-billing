<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Bill;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index() {
        Bill::generateDueBills();
        $clients = Client::with('bills')->orderBy('name')->paginate(20);
        return view('clients.index', compact('clients'));
    }

    public function create() {
        $settings = AppSetting::current();
        return view('clients.form', ['client' => new Client(['share' => $settings->default_share, 'registered_at' => now()])]);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'phone' => 'nullable|string|max:40',
            'notes' => 'nullable|string', 'share' => 'required|numeric|min:0',
            'registered_at' => 'required|date',
        ]);
        $client = Client::create($data + ['active' => true]);
        ActivityLog::create(['message' => "Registered {$client->name}", 'kind' => 'client']);
        Bill::generateDueBills();
        return redirect()->route('clients.index')->with('success', 'Client registered.');
    }

    public function edit(Client $client) { return view('clients.form', compact('client')); }

    public function update(Request $request, Client $client) {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'phone' => 'nullable|string|max:40',
            'notes' => 'nullable|string', 'share' => 'required|numeric|min:0',
            'registered_at' => 'required|date', 'active' => 'nullable|boolean',
        ]);
        DB::transaction(function () use ($client, $data) {
            $client->update($data + ['active' => $data['active'] ?? false]);
            // Reflect the edited monthly share on every bill that is not fully paid.
            // Fully paid bills remain unchanged as historical payment records.
            $client->bills()->whereRaw('amount > paid_amount')->update(['amount' => $client->share]);

            foreach ($client->bills()->whereNotNull('cycle_number')->get() as $bill) {
                $n = (int) $bill->cycle_number;
                $bill->update(['period_start' => $client->cycleStart($n), 'due_at' => $client->cycleDue($n)]);
            }
        });
        ActivityLog::create(['message' => "Updated {$client->name}", 'kind' => 'other']);
        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    public function archive(Client $client) {
        $client->update(['active' => false]);
        return back()->with('success', 'Client archived.');
    }

    public function destroy(Client $client) {
        $name = $client->name; $client->delete();
        ActivityLog::create(['message' => "Deleted client {$name}", 'kind' => 'other']);
        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}
