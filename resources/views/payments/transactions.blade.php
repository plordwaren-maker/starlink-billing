@extends('layouts.app')
@section('title', 'Payments – Starlink Billing')

@section('content')
<div class="pagehead">
    <div>
        <h1>Payments</h1>
        <p>Record and manage client payments</p>
    </div>
    <a class="btn" href="{{ route('bills.index') }}">View bills</a>
</div>

<section class="card">
    <header><h2>Record payment</h2></header>
    <div class="body">
        @if($bills->isEmpty())
            <div class="empty">
                <b>No unpaid bills available</b>
                Register a client or create a bill before recording a payment.
                <div style="margin-top:12px">
                    <a class="btn primary" href="{{ route('clients.create') }}">Register client</a>
                    <a class="btn" href="{{ route('bills.create') }}">Add bill</a>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf
                <div class="two">
                    <div class="field">
                        <label for="bill_id">Bill</label>
                        <select id="bill_id" name="bill_id" required>
                            <option value="">Select a bill</option>
                            @foreach($bills as $bill)
                                <option value="{{ $bill->id }}" @selected(old('bill_id') == $bill->id)>
                                    {{ $bill->client?->name ?? 'Client unavailable' }}
                                    · {{ $bill->due_at?->format('M d, Y') ?? 'No due date' }}
                                    · ₱{{ number_format($bill->balance, 2) }} due
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="amount">Amount received (₱)</label>
                        <input id="amount" type="number" name="amount" min=".01" step=".01"
                               value="{{ old('amount') }}" required>
                    </div>
                </div>
                <div class="two">
                    <div class="field">
                        <label for="received_at">Date received</label>
                        <input id="received_at" type="date" name="received_at"
                               value="{{ old('received_at', now()->toDateString()) }}" required>
                    </div>
                    <div class="field">
                        <label for="method">Method</label>
                        <select id="method" name="method" required>
                            @foreach(['GCash', 'Cash', 'Bank transfer', 'Other'] as $method)
                                <option value="{{ $method }}" @selected(old('method', 'GCash') === $method)>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label for="reference_number">Reference number</label>
                    <input id="reference_number" name="reference_number"
                           value="{{ old('reference_number') }}" maxlength="120">
                </div>
                <button class="btn primary" type="submit">Save payment</button>
            </form>
        @endif
    </div>
</section>

<section class="card">
    <header>
        <h2>Payment history</h2>
        <span class="hint">{{ $transactions->total() }} recorded payments</span>
    </header>
    <div class="tablewrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th><th>Client</th><th>Bill due</th><th class="num">Amount</th>
                    <th>Method</th><th>Reference</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($transactions as $tx)
                @php
                    $relatedClient = $tx->client ?? $tx->bill?->client;
                    $relatedBill = $tx->bill;
                @endphp
                <tr>
                    <td class="nw">{{ $tx->received_at?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $relatedClient?->name ?? 'Deleted / unavailable client' }}</td>
                    <td class="nw">{{ $relatedBill?->due_at?->format('M d, Y') ?? 'Bill unavailable' }}</td>
                    <td class="num">₱{{ number_format($tx->amount, 2) }}</td>
                    <td>{{ $tx->method }}</td>
                    <td>{{ $tx->reference_number ?: '—' }}</td>
                    <td class="nw">
                        <div class="acts">
                            <a class="btn sm" href="{{ route('payments.edit', $tx) }}">Edit</a>
                            <form method="POST" action="{{ route('payments.destroy', $tx) }}"
                                  onsubmit="return confirm('Delete this payment?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn sm danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">No payments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="body">{{ $transactions->links() }}</div>
</section>
@endsection
