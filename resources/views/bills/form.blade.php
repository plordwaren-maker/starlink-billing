@extends('layouts.app')
@section('title', $bill->exists?'Edit Bill':'Add Bill')
@section('content')
<div class="pagehead"><h1>{{ $bill->exists?'Edit bill':'Add manual bill' }}</h1><a class="btn" href="{{ route('bills.index') }}">Back</a></div>
<section class="card"><div class="body" style="padding-top:16px"><form method="POST" action="{{ $bill->exists?route('bills.update',$bill):route('bills.store') }}">
@csrf @if($bill->exists) @method('PUT') @endif
@if(!$bill->exists)<div class="field"><label>Client</label><select name="client_id" required>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach</select></div>@endif
<div class="two"><div class="field"><label>Period starts</label><input type="datetime-local" name="period_start" required value="{{ old('period_start',$bill->period_start?->format('Y-m-d\TH:i')) }}"></div><div class="field"><label>Due</label><input type="datetime-local" name="due_at" required value="{{ old('due_at',$bill->due_at?->format('Y-m-d\TH:i')) }}"></div></div>
<div class="field"><label>Amount (₱)</label><input type="number" name="amount" min="0" step=".01" required value="{{ old('amount',$bill->amount) }}"></div>
<button class="btn primary">Save bill</button></form></div></section>
@endsection
