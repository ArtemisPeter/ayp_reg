@extends('layouts.guest')

@section('title', 'Change password')

@section('content')
    <h4 class="mb-2">Set your own password</h4>
    <p class="mb-4">Your password was set by an administrator. Choose a new one to continue.</p>

    <form method="POST" action="/password/change" class="mb-3">
        @csrf
        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <input type="password" class="form-control" id="password" name="password" minlength="8" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="8" required>
        </div>
        <button class="btn btn-primary d-grid w-100" type="submit">Save password</button>
    </form>
@endsection
