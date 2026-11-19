@extends('layouts.app')
@section('title','Messages – Starlink Billing')
@section('content')
<div class="pagehead"><div><h1>Messages</h1><p>Generate a payment reminder or receipt to copy to your client</p></div></div>
<section class="card"><div class="body" style="padding-top:16px">
<div class="two"><div class="field"><label>Client</label><select id="client">@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}{{ $client->active ? '' : ' (Archived)' }}</option>@endforeach</select></div><div class="field"><label>Billing period</label><select id="bill">@foreach($bills as $bill)<option data-client="{{ $bill->client_id }}" value="{{ $bill->id }}">{{ $bill->period_start->format('M d, Y g:i A') }} – {{ $bill->due_at->format('M d, Y g:i A') }} · ₱{{ number_format($bill->amount, 2) }}</option>@endforeach</select><small class="hint">The message uses the share amount and billing dates saved on this specific bill.</small></div></div>
<div class="field"><label>Message type</label><select id="type"><option value="reminder">Payment reminder</option><option value="receipt">Payment received</option></select></div>
<div class="field"><label>Generated message</label><textarea id="output" rows="15" readonly></textarea></div><button class="btn primary" type="button" id="generate">Generate message</button> <button class="btn" type="button" id="copy">Copy message</button>
</div></section>
@endsection
@push('scripts')
<script>
document.getElementById('generate').addEventListener('click', async () => {
 const params = new URLSearchParams({client_id:document.getElementById('client').value,bill_id:document.getElementById('bill').value,type:document.getElementById('type').value});
 const response = await fetch('{{ route('messages.compose') }}?'+params);
 const data = await response.json(); document.getElementById('output').value = data.message;
});
document.getElementById('copy').addEventListener('click', async () => {
 await navigator.clipboard.writeText(document.getElementById('output').value);
 alert('Message copied.');
});
function filterBillsForClient() {
 const clientId = document.getElementById('client').value;
 const billSelect = document.getElementById('bill');
 let firstVisible = null;
 [...billSelect.options].forEach(option => {
   option.hidden = option.dataset.client !== clientId;
   if (!option.hidden && firstVisible === null) firstVisible = option;
 });
 if (firstVisible) billSelect.value = firstVisible.value;
}
document.getElementById('client').addEventListener('change', filterBillsForClient);
filterBillsForClient();
</script>
@endpush
