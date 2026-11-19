<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in · Starlink Billing</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap">
    <style>
        :root{color-scheme:light;--bg:#f2f5f7;--surface:#fff;--fg:#10181d;--muted:#586671;--line:#d7dfe4;--accent:#0a6c88;--soft:#d9ebf1;--bad:#b0301e}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 15% 10%,#d9ebf1 0,transparent 35%),var(--bg);color:var(--fg);font:14px/1.5 Inter,system-ui,sans-serif}
        .card{width:min(420px,100%);background:var(--surface);border:1px solid var(--line);border-radius:16px;padding:30px;box-shadow:0 18px 50px rgba(16,24,29,.09)}
        .brand{display:flex;align-items:center;gap:10px;font:700 21px Space Grotesk,system-ui,sans-serif;margin-bottom:8px}.mark{display:grid;place-items:center;width:42px;height:42px;border-radius:12px;background:var(--soft);color:var(--accent)}
        h1{font:700 25px Space Grotesk,system-ui,sans-serif;margin:22px 0 4px}p{margin:0 0 22px;color:var(--muted)}
        label{display:block;font-weight:600;margin:14px 0 6px}input{width:100%;padding:12px 13px;border:1px solid var(--line);border-radius:8px;font:inherit;color:var(--fg);background:#fff}input:focus{outline:2px solid #9acbd9;border-color:var(--accent)}
        .remember{display:flex;align-items:center;gap:8px;margin:14px 0;color:var(--muted)}.remember input{width:auto}
        button{width:100%;border:0;border-radius:8px;padding:12px 16px;background:var(--accent);color:#fff;font:600 14px Inter,system-ui,sans-serif;cursor:pointer;margin-top:8px}button:hover{filter:brightness(1.08)}
        .error{color:var(--bad);font-size:13px;margin-top:6px}.alert{padding:10px 12px;border-radius:8px;background:#dcf0e5;color:#1c7a4b;margin:14px 0}.foot{text-align:center;color:var(--muted);font-size:12px;margin-top:20px}
    </style>
</head>
<body>
    <main class="card">
        <div class="brand"><span class="mark"><svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4.5 12a7.5 7.5 0 0 1 15 0"/><path d="M8.5 12a3.5 3.5 0 0 1 7 0"/><circle cx="12" cy="16.5" r="1.4" fill="currentColor"/></svg></span>Starlink Billing</div>
        <h1>Welcome back</h1>
        <p>Sign in to securely manage your billing dashboard.</p>
        @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            @error('email')<div class="error">{{ $message }}</div>@enderror
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            @error('password')<div class="error">{{ $message }}</div>@enderror
            <label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
            <button type="submit">Sign in</button>
        </form>
        <div class="foot">Authorized access only · Starlink Billing</div>
    </main>
</body>
</html>
