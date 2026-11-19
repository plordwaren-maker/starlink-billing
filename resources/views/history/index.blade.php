@extends('layouts.app')
@section('title','History – Starlink Billing')
@section('content')
<div class="pagehead">
    <div><h1>Billing history</h1><p>All generated billing cycles and recorded payments.</p></div>
</div>
<form class="filters" method="GET" action="{{ route('history.index') }}">
    <select name="client_id" aria-label="Filter by client">
        <option value="">All clients</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}" @selected(request('client_id') == $client->id)>{{ $client->name }}</option>
        @endforeach
    </select>
    <input type="month" name="month" value="{{ request('month') }}" aria-label="Filter by month">
    <button class="btn primary" type="submit">Filter</button>
    <a class="btn" href="{{ route('history.index') }}">Clear</a>
</form>
<section class="card" style="margin-bottom:16px">
    <header><h2>Billing cycles</h2><span class="hint">{{ $bills->total() }} billing-cycle records</span></header>
    @if($bills->count())
    <div class="tablewrap"><table><thead><tr><th>Client</th><th>Billing period</th><th>Due date</th><th class="num">Amount</th><th class="num">Paid</th><th class="num">Balance</th><th>Status</th></tr></thead><tbody>
    @foreach($bills as $bill)
    @php
        $isPaid = $bill->balance <= 0.005;
        $isOverdue = $bill->isOverdue();
        $isDueToday = !$isPaid && $bill->isDueToday();
        $historyStatus = $isPaid ? 'paid' : ($isOverdue ? 'overdue' : ($bill->paid_amount > 0 ? 'partial' : 'unpaid'));
    @endphp
    <tr class="{{ $isOverdue ? 'history-overdue' : (($isDueToday || $isPaid) ? 'history-good' : '') }}">
        <td><b>{{ $bill->client?->name ?? 'Client unavailable' }}</b></td>
        <td class="nw">{{ $bill->period_start?->format('M d, Y g:i A') }} – {{ $bill->due_at?->format('M d, Y g:i A') }}</td>
        <td class="nw">{{ $bill->due_at?->format('M d, Y g:i A') }}</td>
        <td class="num">₱{{ number_format($bill->amount,2) }}</td>
        <td class="num">₱{{ number_format($bill->paid_amount,2) }}</td>
        <td class="num">₱{{ number_format($bill->balance,2) }}</td>
        <td><span class="pill {{ $historyStatus === 'paid' || $isDueToday ? 'good' : ($historyStatus === 'overdue' ? 'bad' : 'neutral') }}">{{ $isDueToday ? 'Due today' : ucfirst($historyStatus) }}</span></td>
    </tr>
    @endforeach
    </tbody></table></div>
    <div class="body">{{ $bills->links() }}</div>
    @else
    <div class="empty"><b>No billing records found</b>Billing cycles will appear here when generated.</div>
    @endif
</section>
<section class="card">
    <header><h2>Payment history</h2><span class="hint">{{ $transactions->total() }} payment records</span></header>
    @if($transactions->count())
    <div class="tablewrap"><table><thead><tr><th>Client</th><th>Billing period</th><th>Received</th><th>Method</th><th>Reference</th><th class="num">Amount</th></tr></thead><tbody>
    @foreach($transactions as $payment)
    <tr>
        <td><b>{{ ($payment->client ?? $payment->bill?->client)?->name ?? 'Client unavailable' }}</b></td>
        <td class="nw">{{ $payment->bill?->period_start?->format('M d, Y') }} – {{ $payment->bill?->due_at?->format('M d, Y') }}</td>
        <td class="nw">{{ $payment->received_at?->format('M d, Y') }}</td>
        <td>{{ $payment->method }}</td>
        <td>{{ $payment->reference_number ?: '—' }}</td>
        <td class="num">₱{{ number_format($payment->amount,2) }}</td>
    </tr>
    @endforeach
    </tbody></table></div>
    <div class="body">{{ $transactions->links() }}</div>
    @else
    <div class="empty"><b>No payments recorded</b>Recorded payments will appear here.</div>
    @endif
</section>
@endsection
