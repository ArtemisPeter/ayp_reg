@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
    <h4 class="mb-2">Welcome</h4>
    <p class="mb-4">Sign in with your username and password.</p>

    <form method="POST" action="/login" class="mb-3">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" autofocus required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
    </form>
@endsection
