@extends('layouts.app')
@section('title','Bills – Starlink Billing')
@section('content')
<div class="pagehead"><div><h1>Bills</h1><p>Automatic bills are generated monthly on each client’s registration date.</p></div><a class="btn primary" href="{{ route('bills.create') }}">Add manual bill</a></div>
<form class="card body" method="GET" style="padding-top:14px;margin-bottom:16px"><div class="two"><div class="field"><label>Client</label><select name="client_id"><option value="">All clients</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(request('client_id')==$client->id)>{{ $client->name }}</option>@endforeach</select></div><div class="field"><label>Due month</label><input type="month" name="month" value="{{ request('month') }}"></div></div><button class="btn">Filter</button> <a class="btn" href="{{ route('bills.index') }}">Clear</a></form>
<section class="card"><div class="tablewrap"><table><thead><tr><th>Period</th><th>Due</th><th>Client</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($bills as $bill)<tr><td>{{ $bill->period_start->format('M d') }} – {{ $bill->due_at->format('M d, Y') }}</td><td>{{ $bill->due_at->format('M d, Y h:i A') }}</td><td>{{ $bill->client?->name ?? 'Client unavailable' }}</td><td>₱{{ number_format($bill->amount,2) }}</td><td>₱{{ number_format($bill->paid_amount,2) }}</td><td>₱{{ number_format($bill->balance,2) }}</td><td><span class="pill {{ $bill->status }}">{{ ucfirst($bill->status) }}</span></td><td><a href="{{ route('bills.edit',$bill) }}">Edit</a> <form style="display:inline" method="POST" action="{{ route('bills.destroy',$bill) }}" onsubmit="return confirm('Delete bill?')">@csrf @method('DELETE')<button class="btn danger">Delete</button></form></td></tr>
@empty<tr><td colspan="8">No bills found.</td></tr>@endforelse
</tbody></table></div><div class="body">{{ $bills->links() }}</div></section>
@endsection
