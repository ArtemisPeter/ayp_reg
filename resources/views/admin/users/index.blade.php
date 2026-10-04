@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">Add a user</h5>
        <div class="card-body">
            <form method="POST" action="/admin/users" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label" for="name">Full name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" id="username" name="username" value="{{ old('username') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="role">Role</label>
                    <select class="form-select" id="role" name="role" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ str($role->value)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="circuit_id">Circuit (Circuit Presidents only)</label>
                    <select class="form-select" id="circuit_id" name="circuit_id">
                        <option value="">None</option>
                        @foreach ($circuits as $circuit)
                            <option value="{{ $circuit->id }}" @selected((string) old('circuit_id') === (string) $circuit->id)>{{ $circuit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="password">Temporary password</label>
                    <input class="form-control" id="password" name="password" type="text" minlength="8" required>
                    <div class="form-text">They must replace it at first sign-in.</div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Add user</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Users</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Username</th><th>Role</th><th>Circuit</th><th>Reset password</th><th></th></tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ str($user->role->value)->replace('_', ' ')->title() }}</td>
                            <td>{{ $user->circuit?->name }}</td>
                            <td>
                                <form method="POST" action="/admin/users/{{ $user->id }}/reset-password" class="d-flex gap-2">
                                    @csrf
                                    <input class="form-control form-control-sm" name="password" type="text" minlength="8" placeholder="New temporary password" required>
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">Reset</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="/admin/users/{{ $user->id }}" onsubmit="return confirm('Remove this user? They will no longer be able to sign in.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
