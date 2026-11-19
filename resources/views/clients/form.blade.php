@extends('layouts.app')
@section('title', $client->exists?'Edit Client':'Register Client')
@section('content')
<div class="pagehead"><h1>{{ $client->exists?'Edit client':'Register client' }}</h1><a class="btn" href="{{ route('clients.index') }}">Back</a></div>
<section class="card"><div class="body" style="padding-top:16px">
<form method="POST" action="{{ $client->exists?route('clients.update',$client):route('clients.store') }}">
@csrf @if($client->exists) @method('PUT') @endif
<div class="field"><label>Full name</label><input name="name" required value="{{ old('name',$client->name) }}"></div>
<div class="two"><div class="field"><label>Phone or GCash</label><input name="phone" value="{{ old('phone',$client->phone) }}"></div><div class="field"><label>Monthly share (₱)</label><input type="number" min="0" step=".01" name="share" required value="{{ old('share',$client->share) }}"></div></div>
<div class="field"><label>Registered at (date and time)</label><input type="datetime-local" name="registered_at" required value="{{ old('registered_at',$client->registered_at?->format('Y-m-d\TH:i')) }}"></div>
<div class="field"><label>Notes</label><textarea name="notes" rows="3">{{ old('notes',$client->notes) }}</textarea></div>
@if($client->exists)<label><input type="checkbox" name="active" value="1" {{ old('active',$client->active)?'checked':'' }}> Active, include in automatic billing</label>@endif
<div style="margin-top:16px"><button class="btn primary">Save client</button></div>
</form></div></section>
@endsection
