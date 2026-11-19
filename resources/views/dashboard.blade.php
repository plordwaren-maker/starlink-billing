@extends('layouts.app')
@section('title','Dashboard – Starlink Billing')
@section('content')
<div class="pagehead"><div><h1>Dashboard</h1><p>{{ now()->format('F Y') }} · billed monthly on each client’s registration date</p></div><div class="actions"><a class="btn" href="{{ route('clients.create') }}">Register client</a><a class="btn primary" href="{{ route('payments.index') }}">Record payment</a></div></div>
<div class="tiles">
    <div class="tile"><div class="k">Collected in {{ now()->format('M') }}</div><div class="v">₱{{ number_format($collected,2) }}</div><div class="muted">{{ $paidBillsThisMonth }} of {{ $billsDueThisMonth }} bills due this month are paid</div></div>
    <div class="tile"><div class="k">Outstanding</div><div class="v">₱{{ number_format($currentOutstanding,2) }}</div><div class="muted">{{ $followUpBills->filter(fn($b) => $b->balance > 0.005)->count() }} current unpaid subscription{{ $followUpBills->filter(fn($b) => $b->balance > 0.005)->count() === 1 ? '' : 's' }}</div></div>
    <div class="tile"><div class="k">Overdue</div><div class="v">₱{{ number_format($overdue->sum(fn($b)=>$b->balance),2) }}</div><div class="muted">{{ $overdue->count() }} overdue · {{ $dueSoon->count() }} due within {{ $settings->reminder_days ?? 3 }} days</div></div>
    <div class="tile"><div class="k">Active clients</div><div class="v">{{ $activeClientsCount }}</div><div class="muted">₱{{ number_format($clients->where('active',true)->sum('share'),2) }} expected per month</div></div>
</div>
<div class="grid2">
<section class="card"><header><h2>Needs follow-up</h2><span class="hint">Most urgent first. Paid clients stay listed with time left.</span></header>
@forelse($followUpBills as $bill)
<div class="row {{ $bill->balance <= 0.005 ? 'paid-on-time' : ($bill->isOverdue() ? 'unpaid-overdue' : ($bill->isDueToday() ? 'due-today' : '')) }}"><div class="who"><strong>{{ $bill->client?->name ?? 'Client unavailable' }}</strong><span>{{ $bill->period_start?->format('M d') }} – {{ $bill->due_at?->format('M d') }} · {{ $bill->due_at?->diffForHumans() }} ({{ $bill->due_at?->format('M d, g:i A') }})</span></div><strong class="amt">@if($bill->balance <= 0.005)Paid in full @else ₱{{ number_format($bill->balance,2) }} due @endif</strong><span class="pill {{ $bill->status }}">{{ ucfirst($bill->status) }}</span>
<div class="acts"><a class="btn sm" href="{{ route('messages.index') }}">Copy message</a>@if($bill->balance > 0.005)<form method="POST" action="{{ route('payments.store') }}" onsubmit="return confirm('Mark this bill as paid?')">@csrf<input type="hidden" name="bill_id" value="{{ $bill->id }}"><input type="hidden" name="amount" value="{{ number_format($bill->balance,2,'.','') }}"><input type="hidden" name="received_at" value="{{ now()->toDateString() }}"><input type="hidden" name="method" value="GCash"><button class="btn sm primary" type="submit">Mark paid</button></form>@endif</div></div>
@empty<div class="body empty"><b>No active subscriptions</b>Register a client to start monthly follow-up.</div>@endforelse
</section>
<section class="card"><header><h2>Billed vs collected</h2></header><div class="body"><svg class="chart" viewBox="0 0 640 230" role="img" aria-label="Billed and collected per month" width="100%">
@php $maxValue=max(1,collect($chartData)->max(fn($d)=>max($d['billed'],$d['paid']))); $step=max(1,ceil($maxValue/4/100)*100); $top=max($step,ceil($maxValue/$step)*$step); $plotHeight=192; @endphp
@for($i=0;$i<=4;$i++)@php $value=$top*$i/4; $y=12+$plotHeight*(1-$value/$top); @endphp<line class="grid" x1="52" x2="632" y1="{{ $y }}" y2="{{ $y }}"/><text x="44" y="{{ $y+4 }}" text-anchor="end">₱{{ number_format($value,0) }}</text>@endfor
@foreach($chartData as $i=>$data)@php $center=52+(580/6)*$i+(580/12); $bw=24; $by=12+$plotHeight*(1-$data['billed']/$top); $py=12+$plotHeight*(1-$data['paid']/$top); @endphp<rect class="b-billed" x="{{ $center-$bw-2 }}" y="{{ $by }}" width="{{ $bw }}" height="{{ max(0,204-$by) }}"/><rect class="b-paid" x="{{ $center+2 }}" y="{{ $py }}" width="{{ $bw }}" height="{{ max(0,204-$py) }}"/><text x="{{ $center }}" y="222" text-anchor="middle">{{ $data['label'] }}</text>@endforeach
</svg><div class="legend"><span><i style="background:var(--bar)"></i>Billed</span><span><i style="background:var(--accent)"></i>Collected</span></div></div></section>
</div>
<div class="grid2b">
<section class="card"><header><h2>Recent payments</h2><a href="{{ route('history.index') }}" class="hint">View all</a></header>
@forelse($recentPayments as $payment)@php $person=$payment->client ?? $payment->bill?->client; @endphp<div class="row"><div class="who"><strong>{{ $person?->name ?? 'Client unavailable' }}</strong><span>{{ $payment->bill?->period_start?->format('M d') }} – {{ $payment->bill?->due_at?->format('M d') }} · {{ $payment->method }} · {{ $payment->received_at?->format('M d, Y') }}</span></div><strong class="amt">₱{{ number_format($payment->amount,2) }}</strong></div>@empty<div class="empty"><b>No payments yet</b>Recorded payments will show up here.</div>@endforelse
</section>
<section class="card"><header><h2>Activity</h2></header><div class="feed">@forelse($activity as $item)<div><i class="{{ $item->kind }}"></i><span>{{ $item->message }}<small>{{ $item->created_at?->diffForHumans() }}</small></span></div>@empty<div class="empty"><b>Nothing yet</b>Registrations and payments are listed here.</div>@endforelse</div></section>
</div>
@endsection
