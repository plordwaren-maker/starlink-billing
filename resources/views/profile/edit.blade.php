@extends('layouts.app')

@section('title', 'Administrator Profile · Starlink Billing')

@section('content')
<div class="pagehead">
    <div>
        <h1>Administrator Profile</h1>
        <p>Update your account details and sign-in password.</p>
    </div>
</div>

<div class="card" style="max-width:680px">
    <header><h2>Profile details</h2></header>
    <div class="body">
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="name">Administrator name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
            </div>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
            </div>
            <h3 class="sec">Change password</h3>
            <p class="hint">Leave these fields blank if you do not want to change your password. Use at least 12 characters.</p>
            <div class="field">
                <label for="current_password">Current password</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password">
            </div>
            <div class="two">
                <div class="field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" minlength="12" autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="12" autocomplete="new-password">
                </div>
            </div>
            <div class="acts" style="justify-content:flex-end;margin-top:16px">
                <button class="btn primary" type="submit">Save profile</button>
            </div>
        </form>
    </div>
</div>
@endsection
