@extends('layouts.app')
@section('title','Edit Payment')
@section('content')
<div class="pagehead"><h1>Edit payment</h1><a class="btn" href="{{ route('payments.index') }}">Back</a></div>
<section class="card"><div class="body" style="padding-top:16px"><form method="POST" action="{{ route('payments.update',$payment) }}">@csrf @method('PUT')
<p>{{ $payment->client?->name ?? 'Client unavailable' }} · {{ $payment->bill?->due_at?->format('M d, Y') ?? 'Bill unavailable' }}</p>
<div class="two"><div class="field"><label>Amount</label><input type="number" min=".01" step=".01" name="amount" value="{{ $payment->amount }}" required></div><div class="field"><label>Date received</label><input type="date" name="received_at" value="{{ $payment->received_at->format('Y-m-d') }}" required></div></div>
<div class="two"><div class="field"><label>Method</label><select name="method">@foreach(['GCash','Cash','Bank transfer','Other'] as $method)<option @selected($payment->method===$method)>{{ $method }}</option>@endforeach</select></div><div class="field"><label>Reference number</label><input name="reference_number" value="{{ $payment->reference_number }}"></div></div>
<button class="btn primary">Update payment</button></form></div></section>
@endsection
