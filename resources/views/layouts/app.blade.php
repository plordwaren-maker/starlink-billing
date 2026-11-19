<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Starlink Billing')</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap">
    <style>

/* Layout: sticky top bar with tabs and a notification bell, an alert strip under it, then dashboard (follow-ups + chart), clients, payments (bills + transactions), messages and setup. */
:root{
  --bg:#f2f5f7; --surface:#ffffff; --surface2:#e9eef1; --fg:#10181d; --muted:#586671; --line:#d7dfe4;
  --accent:#0a6c88; --accent-fg:#ffffff; --accent-soft:#d9ebf1;
  --good:#1c7a4b; --good-soft:#dcf0e5; --warn:#8f5606; --warn-soft:#f8ecd2; --bad:#b0301e; --bad-soft:#f8dfd9;
  --bar:#c3ced5;
  --font-head:"Space Grotesk",system-ui,sans-serif; --font-body:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;
}
@media (prefers-color-scheme: dark){:root:not([data-theme="light"]){
  --bg:#0b1013; --surface:#131a1f; --surface2:#1a242a; --fg:#e7eef2; --muted:#8b99a3; --line:#25313a;
  --accent:#52b9d6; --accent-fg:#03202a; --accent-soft:#11303b;
  --good:#4cc38a; --good-soft:#12301f; --warn:#e3a94b; --warn-soft:#352a10; --bad:#f2745f; --bad-soft:#3a1b15;
  --bar:#2f3e48; color-scheme:dark}}
:root[data-theme="dark"]{
  --bg:#0b1013; --surface:#131a1f; --surface2:#1a242a; --fg:#e7eef2; --muted:#8b99a3; --line:#25313a;
  --accent:#52b9d6; --accent-fg:#03202a; --accent-soft:#11303b;
  --good:#4cc38a; --good-soft:#12301f; --warn:#e3a94b; --warn-soft:#352a10; --bad:#f2745f; --bad-soft:#3a1b15;
  --bar:#2f3e48; color-scheme:dark}

*{box-sizing:border-box}
body{background:var(--bg);color:var(--fg);font:14px/1.5 var(--font-body);min-height:100%}
body::before{content:"";position:fixed;inset:0 0 auto;height:env(safe-area-inset-top,0px);z-index:10;background:var(--bg)}
h1,h2,h3{font-family:var(--font-head);margin:0;text-wrap:balance;letter-spacing:-.01em}
h2{font-size:18px} h3{font-size:15px}
button,input,select,textarea{font:inherit;color:inherit}
.inner{max-width:1120px;margin:0 auto;padding-left:max(16px,env(safe-area-inset-left,0px));padding-right:max(16px,env(safe-area-inset-right,0px))}
.top{position:sticky;top:env(safe-area-inset-top,0px);z-index:20;background:var(--bg);border-bottom:1px solid var(--line)}
.bar{display:flex;align-items:center;gap:14px;padding-block:10px}
.brand{display:flex;align-items:center;gap:9px;font-family:var(--font-head);font-weight:700;font-size:17px;color:var(--fg);text-decoration:none;white-space:nowrap}
.brand svg{color:var(--accent)}
nav{display:flex;gap:2px;overflow-x:auto;margin-left:8px;min-width:0;scrollbar-width:none}
nav a{padding:7px 12px;border-radius:6px;color:var(--muted);text-decoration:none;font-weight:500;white-space:nowrap}
nav a:hover{color:var(--fg);background:var(--surface2)}
nav a[aria-current="page"]{color:var(--accent);background:var(--accent-soft)}
.bellwrap{margin-left:auto;position:relative}
.bell{list-style:none;position:relative;display:flex;align-items:center;justify-content:center;width:38px;height:38px;border:1px solid var(--line);background:var(--surface);border-radius:8px;cursor:pointer}
.bell .dot{position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 5px;border-radius:9px;background:var(--bad);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center}
.bellwrap details[open] .panel-pop{display:block}.bellwrap summary::-webkit-details-marker{display:none}
.panel-pop{display:none;position:absolute;right:0;top:calc(100% + 8px);width:min(400px,calc(100vw - 32px));max-height:70vh;overflow:auto;background:var(--surface);border:1px solid var(--line);border-radius:10px;box-shadow:0 12px 32px rgba(0,0,0,.18);z-index:40}
.pop-head{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:12px 14px;border-bottom:1px solid var(--line)}
.note{padding:11px 14px;border-bottom:1px solid var(--line);display:flex;gap:10px;align-items:flex-start}
.note:last-child{border-bottom:0}
.note .ic{flex:none;width:8px;height:8px;border-radius:50%;margin-top:7px;background:var(--muted)}
.note .nb{flex:1;min-width:0}
.note.unread{background:var(--accent-soft)}
.note small{display:block;color:var(--muted)}
.note .acts{margin-top:6px}
@media (max-width:760px){.bar{flex-wrap:wrap}nav{order:3;width:100%;margin-left:0}}

.alertbar{display:block;text-decoration:none;border-radius:8px;padding:10px 14px;margin-top:14px;font-weight:500;overflow-wrap:anywhere}
.alertbar.bad{background:var(--bad-soft);color:var(--bad)}
.alertbar.warn{background:var(--warn-soft);color:var(--warn)}
.alertbar b{font-weight:700}

main{padding-block:22px 60px}
.pagehead{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
.pagehead p{margin:2px 0 0;color:var(--muted)}
.banner{background:var(--warn-soft);color:var(--warn);border-radius:8px;padding:10px 12px;margin-bottom:16px;font-weight:500}

.tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:12px;margin-bottom:16px}
.tile{background:var(--surface);border:1px solid var(--line);border-radius:10px;padding:14px 16px;min-width:0}
.tile .k{color:var(--muted);font-size:12px;font-weight:600;letter-spacing:.04em;text-transform:uppercase}
.tile .v{font-family:var(--font-head);font-size:26px;font-weight:700;margin-top:4px;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
.tile .s{color:var(--muted);font-size:13px;margin-top:2px}
.tile.bad .v{color:var(--bad)}

.grid2{display:grid;grid-template-columns:minmax(0,3fr) minmax(0,2fr);gap:16px;margin-bottom:16px}
.grid2b{display:grid;grid-template-columns:minmax(0,3fr) minmax(0,2fr);gap:16px}
@media (max-width:900px){.grid2,.grid2b{grid-template-columns:minmax(0,1fr)}}
.card{background:var(--surface);border:1px solid var(--line);border-radius:10px;min-width:0}
.card>header{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px 10px}
.card .body{padding:0 16px 16px}
.list{display:flex;flex-direction:column}
.row{display:flex;flex-wrap:wrap;align-items:center;gap:8px 12px;justify-content:space-between;padding:11px 16px;border-top:1px solid var(--line)}
.row .who{min-width:0;flex:1 1 160px}
.row .who b{display:block;overflow-wrap:anywhere}
.row .who span{color:var(--muted);font-size:13px}
.row .amt{font-variant-numeric:tabular-nums;font-weight:600}
.row .amt.dim{font-weight:500;color:var(--muted);font-size:13px}
.acts{display:flex;gap:6px;flex-wrap:wrap}
.empty{padding:26px 16px;text-align:center;color:var(--muted)}
.empty b{display:block;color:var(--fg);font-family:var(--font-head);font-size:16px;margin-bottom:4px}

.row.unpaid-overdue{background:var(--bad-soft);border-left:4px solid var(--bad)}.row.unpaid-overdue .amt{color:var(--bad)}
.row.due-today,.row.paid-on-time{background:var(--good-soft);border-left:4px solid var(--good)}
.row.due-today .amt,.row.paid-on-time .amt{color:var(--good)}
table tr.history-good{background:var(--good-soft)}
table tr.history-overdue{background:var(--bad-soft)}

.pill{display:inline-block;padding:2px 9px;border-radius:999px;font-size:12px;font-weight:600;white-space:nowrap}
.pill.good{background:var(--good-soft);color:var(--good)}
.pill.bad{background:var(--bad-soft);color:var(--bad)}
.pill.warn{background:var(--warn-soft);color:var(--warn)}
.pill.neutral{background:var(--surface2);color:var(--muted)}

.btn{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--line);background:var(--surface);border-radius:7px;padding:8px 14px;font-weight:600;cursor:pointer;line-height:1.2}
.btn:hover{background:var(--surface2)}
.btn.primary{background:var(--accent);color:var(--accent-fg);border-color:var(--accent)}
.btn.primary:hover{filter:brightness(1.08)}
.btn.sm{padding:5px 10px;font-size:13px}
.btn.danger{color:var(--bad)}
.btn.danger.armed{background:var(--bad);color:#fff;border-color:var(--bad)}
.btn.x{padding:2px 8px;font-size:16px;line-height:1.2;border-color:transparent;background:transparent;color:var(--muted)}
:focus-visible{outline:2px solid var(--accent);outline-offset:2px}

.tablewrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-variant-numeric:tabular-nums}
th{text-align:left;font-size:12px;letter-spacing:.04em;text-transform:uppercase;color:var(--muted);font-weight:600;padding:9px 16px;border-top:1px solid var(--line);white-space:nowrap}
td{padding:10px 16px;border-top:1px solid var(--line);vertical-align:middle}
td.num,th.num{text-align:right}
td.nw{white-space:nowrap}
.filters{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px}
.filters select{width:auto;min-width:160px}

.field{display:flex;flex-direction:column;gap:4px;margin-bottom:12px;min-width:0}
.field label{font-weight:600;font-size:13px}
.field small{color:var(--muted)}
input,select,textarea{width:100%;background:var(--surface);border:1px solid var(--line);border-radius:7px;padding:8px 10px;min-width:0}
input[type=checkbox]{width:auto}
.check{display:flex;align-items:center;gap:8px;font-weight:500}
.two{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}
@media (max-width:520px){.two{grid-template-columns:1fr}}
.sec{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin:8px 0 10px;padding-top:12px;border-top:1px solid var(--line)}
.prev{background:var(--surface2);border-radius:8px;padding:10px 14px;margin-bottom:14px}
.prow{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:5px 0}
.prow>span{color:var(--muted);flex:none}
.prow b{text-align:right;font-variant-numeric:tabular-nums;font-weight:600}
.prow small{font-weight:400}

.overlay{position:fixed;inset:0;z-index:50;background:rgba(5,10,14,.55);display:flex;align-items:flex-start;justify-content:center;padding:max(16px,env(safe-area-inset-top,0px)) max(16px,env(safe-area-inset-right,0px)) max(16px,env(safe-area-inset-bottom,0px)) max(16px,env(safe-area-inset-left,0px));overflow:auto}
.dialog{background:var(--surface);border:1px solid var(--line);border-radius:12px;width:min(580px,100%);margin-top:4vh;padding:20px}
.dialog h2{margin-bottom:14px}
.dialog .foot{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;margin-top:6px}

.msgwrap{display:grid;grid-template-columns:minmax(0,2fr) minmax(0,3fr);gap:16px}
@media (max-width:900px){.msgwrap{grid-template-columns:minmax(0,1fr)}}
.msgout{min-height:520px;white-space:pre-wrap;line-height:1.55;resize:vertical}
.hint{color:var(--muted);font-size:13px}

svg.chart text{fill:var(--muted);font-size:11px;font-family:var(--font-body)}
svg.chart .grid{stroke:var(--line);stroke-width:1}
svg.chart .b-billed{fill:var(--bar)}
svg.chart .b-paid{fill:var(--accent)}
.legend{display:flex;gap:14px;color:var(--muted);font-size:13px;margin-top:6px}
.legend i{display:inline-block;width:10px;height:10px;border-radius:2px;margin-right:6px;vertical-align:-1px}

.feed{display:flex;flex-direction:column}
.feed div{display:flex;gap:10px;padding:9px 16px;border-top:1px solid var(--line)}
.feed i{flex:none;width:8px;height:8px;border-radius:50%;margin-top:7px;background:var(--muted)}
.feed i.payment{background:var(--good)} .feed i.client{background:var(--accent)}
.feed small{display:block;color:var(--muted)}

#toast{position:fixed;left:50%;transform:translateX(-50%);bottom:max(20px,env(safe-area-inset-bottom,0px));background:var(--fg);color:var(--bg);padding:10px 16px;border-radius:8px;font-weight:500;z-index:60;max-width:calc(100vw - 32px);box-shadow:0 8px 24px rgba(0,0,0,.25)}
@media (prefers-reduced-motion:no-preference){#toast{animation:pop .18s ease-out}@keyframes pop{from{opacity:0;transform:translate(-50%,6px)}}}

    </style>
    @stack('styles')
</head>
<body>
<header class="top">
    <div class="inner bar">
        <a class="brand" href="{{ route('dashboard') }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4.5 12a7.5 7.5 0 0 1 15 0"/><path d="M8.5 12a3.5 3.5 0 0 1 7 0"/><circle cx="12" cy="16.5" r="1.4" fill="currentColor"/></svg>
            Starlink Billing
        </a>
        <nav aria-label="Sections">
            <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
            <a href="{{ route('clients.index') }}" @if(request()->routeIs('clients.*')) aria-current="page" @endif>Clients</a>
            <a href="{{ route('payments.index') }}" @if(request()->routeIs('payments.*')) aria-current="page" @endif>Payments</a>
            <a href="{{ route('history.index') }}" @if(request()->routeIs('history.*')) aria-current="page" @endif>History</a>
            <a href="{{ route('messages.index') }}" @if(request()->routeIs('messages.*')) aria-current="page" @endif>Messages</a>
            <a href="{{ route('settings.edit') }}" @if(request()->routeIs('settings.*')) aria-current="page" @endif>Setup</a>
            <a href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>Profile</a>
        </nav>
        <div class="bellwrap"><details>
            <summary class="bell" aria-label="Notifications"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 0 1 12 0c0 6 2.5 7.5 2.5 7.5h-17S6 15 6 9z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>@if(($unreadNotificationCount ?? 0)>0)<span class="dot">{{ $unreadNotificationCount }}</span>@endif</summary>
            <div class="panel-pop"><div class="pop-head"><strong>Notifications</strong><form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn sm">Mark all read</button></form></div>
            @forelse(($notifications ?? collect()) as $notification)<div class="note {{ $notification->is_read?'':'unread' }}"><i class="ic"></i><div class="nb">{{ $notification->message }}<small>{{ $notification->created_at?->diffForHumans() }}</small>@if(!$notification->is_read)<form method="POST" action="{{ route('notifications.read',$notification) }}">@csrf<button class="btn sm">Mark read</button></form>@endif</div></div>
            @empty<div class="empty">No notifications yet.</div>@endforelse</div>
        </details></div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button class="btn sm" type="submit" title="Sign out">Sign out</button>
        </form>
    </div>
</header>
<div class="inner">
    @if(session('success'))<div class="alertbar" style="background:var(--good-soft);color:var(--good)">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="banner"><b>Please check the form:</b>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <main>@yield('content')</main>
</div>
@stack('scripts')
</body>
</html>
