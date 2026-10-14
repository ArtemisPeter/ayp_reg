@extends('layouts.guest')

@section('title', 'Change password')

@section('content')
    <h1 class="duyog-auth-heading">Set your own password</h1>
    <p class="mb-4">Your password was set by an administrator. Choose a new one to continue.</p>

    <form method="POST" action="/password/change" data-busy-label="Saving&hellip;">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <div class="duyog-field has-toggle">
                <i class="bx bx-lock-alt" aria-hidden="true"></i>
                <input type="password" class="form-control" id="password" name="password" minlength="8" autocomplete="new-password" aria-describedby="password-help" required autofocus>
                <button type="button" class="duyog-field-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false"><i class="bx bx-show" aria-hidden="true"></i></button>
            </div>
            <p id="password-help" class="duyog-field-hint">At least 8 characters, with letters and numbers.</p>
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <div class="duyog-field has-toggle">
                <i class="bx bx-check-shield" aria-hidden="true"></i>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                <button type="button" class="duyog-field-toggle" data-password-toggle="password_confirmation" aria-label="Show password" aria-pressed="false"><i class="bx bx-show" aria-hidden="true"></i></button>
            </div>
        </div>
        <button class="btn btn-primary w-100 duyog-auth-submit" type="submit">Save password</button>
    </form>
@endsection
