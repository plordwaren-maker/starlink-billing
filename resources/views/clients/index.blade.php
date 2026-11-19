@extends('layouts.app')
@section('title','Clients – Starlink Billing')
@section('content')
<div class="pagehead"><div><h1>Clients</h1><p>Everyone sharing the Starlink subscription</p></div><a class="btn primary" href="{{ route('clients.create') }}">Register client</a></div>
<section class="card"><div class="tablewrap"><table><thead><tr><th>Client</th><th>Contact</th><th>Monthly share</th><th>Registered</th><th>Current balance</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($clients as $client)<tr>
<td><strong>{{ $client->name }}</strong><div class="hint">{{ $client->notes }}</div></td><td>{{ $client->phone ?: '—' }}</td><td>₱{{ number_format($client->share,2) }}</td><td>{{ $client->registered_at->format('M d, Y h:i A') }}</td>
<td>₱{{ number_format(optional($client->currentFollowUpBill())->balance ?? 0,2) }}</td><td>{{ $client->active?'Active':'Archived' }}</td>
<td><div class="actions"><a class="btn" href="{{ route('clients.edit',$client) }}">Edit</a><form method="POST" action="{{ route('clients.destroy',$client) }}" onsubmit="return confirm('Delete this client and related bills/payments?')">@csrf @method('DELETE')<button class="btn danger">Delete</button></form></div></td></tr>
@empty<tr><td colspan="7">No clients registered yet.</td></tr>@endforelse
</tbody></table></div><div class="body">{{ $clients->links() }}</div></section>
@endsection
