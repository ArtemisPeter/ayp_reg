@extends('layouts.app')

@section('title', 'Users')

@section('content')
    @include('partials.page-head', [
        'icon' => 'bx-user',
        'title' => 'Users',
        'text' => 'Staff accounts and what each person can do. New users replace their temporary password at first sign-in.',
    ])

    <div class="card mb-4">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-user-plus" aria-hidden="true"></i> Add a user</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/users" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label" for="name">Full name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="off" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" id="username" name="username" value="{{ old('username') }}" autocomplete="off" autocapitalize="none" spellcheck="false" required>
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
                    <input class="form-control" id="password" name="password" type="text" minlength="8" autocomplete="off" aria-describedby="password-help" required>
                    <div class="form-text" id="password-help">At least 8 characters, with letters and numbers. They must replace it at first sign-in.</div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-user-plus" aria-hidden="true"></i> Add user</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-group" aria-hidden="true"></i> Staff accounts</h2>
            <span class="duyog-panel-meta">{{ $users->count() }} {{ Str::plural('user', $users->count()) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th scope="col">Name</th><th scope="col">Role</th><th scope="col">Circuit</th><th scope="col">Reset password</th><th scope="col"><span class="visually-hidden">Remove</span></th></tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($users as $user)
                        <tr>
                            <td class="text-nowrap"><span class="duyog-cell-strong">{{ $user->name }}</span><span class="duyog-cell-sub">{{ $user->username }}</span></td>
                            <td class="text-nowrap"><span class="badge {{ $user->role === \App\Enums\Role::Admin ? 'bg-label-warning' : 'bg-label-primary' }}">{{ str($user->role->value)->replace('_', ' ')->title() }}</span></td>
                            <td class="text-nowrap">{{ $user->circuit?->name }}</td>
                            <td>
                                <form method="POST" action="/admin/users/{{ $user->id }}/reset-password" class="duyog-inline-form">
                                    @csrf
                                    <input class="form-control form-control-sm" name="password" type="text" minlength="8" placeholder="New temporary password" aria-label="New temporary password for {{ $user->name }}" autocomplete="off" required>
                                    <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bx bx-key" aria-hidden="true"></i> Reset</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="/admin/users/{{ $user->id }}" onsubmit="return confirm('Remove this user? They will no longer be able to sign in.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Remove {{ $user->name }}"><i class="bx bx-trash" aria-hidden="true"></i> Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
