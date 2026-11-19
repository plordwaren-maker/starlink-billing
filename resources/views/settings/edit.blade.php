@extends('layouts.app')
@section('title','Setup – Starlink Billing')
@section('content')
<div class="pagehead"><div><h1>Setup</h1><p>Payment details and billing warnings</p></div></div>
<section class="card"><div class="body" style="padding-top:16px"><form method="POST" action="{{ route('settings.update') }}">@csrf @method('PUT')
<div class="field"><label>Your name (receiver)</label><input name="receiver_name" required value="{{ old('receiver_name',$settings->receiver_name) }}"></div>
<div class="two"><div class="field"><label>GCash number</label><input name="gcash_number" required value="{{ old('gcash_number',$settings->gcash_number) }}"></div><div class="field"><label>Default monthly share for new clients (₱)</label><input type="number" min="0" step=".01" name="default_share" required value="{{ old('default_share',$settings->default_share) }}"></div></div><small class="hint">This default is used when registering a new client. To change an existing client’s monthly share, edit that client on the Clients page.</small>
<div class="field"><label>Warn this many days before due</label><input type="number" min="0" max="30" name="reminder_days" required value="{{ old('reminder_days',$settings->reminder_days) }}"></div>
<div class="field"><label>Reason for billing increase</label><textarea name="increase_reason" rows="6">{{ old('increase_reason',$settings->increase_reason) }}</textarea></div>
<button class="btn primary">Save setup</button></form></div></section>
@endsection
