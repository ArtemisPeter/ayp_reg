@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <h1 class="duyog-auth-heading">Welcome back</h1>
    <p class="mb-4">Sign in with the username and password issued to you.</p>

    <form method="POST" action="/login" data-busy-label="Signing in&hellip;">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <div class="duyog-field">
                <i class="bx bx-user" aria-hidden="true"></i>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" autocomplete="username" autocapitalize="none" spellcheck="false" @if ($errors->any()) aria-invalid="true" @endif autofocus required>
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="duyog-field has-toggle">
                <i class="bx bx-lock-alt" aria-hidden="true"></i>
                <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" aria-describedby="password-caps" @if ($errors->any()) aria-invalid="true" @endif required>
                <button type="button" class="duyog-field-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false"><i class="bx bx-show" aria-hidden="true"></i></button>
            </div>
            <p id="password-caps" class="duyog-field-hint is-warning" data-caps-hint="password" hidden><i class="bx bx-error" aria-hidden="true"></i> Caps Lock is on</p>
        </div>
        <button class="btn btn-primary w-100 duyog-auth-submit" type="submit">Sign in</button>
    </form>

    <p class="duyog-auth-help"><i class="bx bx-help-circle" aria-hidden="true"></i> Forgot your password? Ask an administrator to reset it.</p>
@endsection
