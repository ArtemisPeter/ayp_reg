@extends('layouts.app')

@section('title', 'Attendees')

@section('content')
    @php($canManage = in_array(auth()->user()->role, [\App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
    @if ($canManage)
        <div class="card mb-4">
            <h5 class="card-header">Register someone on-site</h5>
            <div class="card-body">
                <form method="POST" action="/attendees" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label" for="first_name">First name</label>
                        <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="middle_name">Middle name (optional)</label>
                        <input class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="last_name">Last name</label>
                        <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="local_church_id">Local Church</label>
                        <select class="form-select" id="local_church_id" name="local_church_id" required>
                            @foreach ($churches as $church)
                                <option value="{{ $church->id }}" @selected((string) old('local_church_id') === (string) $church->id)>{{ $church->circuit->name }}: {{ $church->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="birthday">Birthday</label>
                        <input class="form-control" id="birthday" name="birthday" type="date" value="{{ old('birthday') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="baptismal">Baptismal</label>
                        <select class="form-select" id="baptismal" name="baptismal" required>
                            <option value="Yes" @selected(old('baptismal') === 'Yes')>Yes</option>
                            <option value="No" @selected(old('baptismal') === 'No')>No</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="type">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="Y" @selected(old('type') === 'Y')>Youth</option>
                            <option value="P" @selected(old('type') === 'P')>Pastor</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Register on-site</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($canManage || auth()->user()->role === \App\Enums\Role::CircuitPresident)
        <div class="card mb-4">
            <h5 class="card-header">{{ $canManage ? 'Upload an on-site roster' : 'Upload a roster' }}</h5>
            <div class="card-body">
                @if ($canManage)
                    <p class="text-muted">Upload a CSV of people registered on-site, for example after an internet outage. It can cover any Circuit. If any row has a problem, nothing is registered and you will see every problem below.</p>
                @else
                    <p class="text-muted">Upload the CSV your Local Church Presidents filled in, after payment is confirmed. If any row has a problem, nothing is registered and you will see every problem below.</p>
                @endif
                <form method="POST" action="/registrations/upload" enctype="multipart/form-data" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label" for="file">CSV file</label>
                        <input class="form-control" id="file" name="file" type="file" accept=".csv,text/csv" required>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if (session('roster_errors'))
        <div class="card mb-4 border border-danger">
            <h5 class="card-header text-danger">The file was not accepted. Nothing was registered.</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Row</th><th>Problem</th></tr></thead>
                    <tbody>
                        @foreach (session('roster_errors') as $error)
                            <tr>
                                <td>Row {{ $error['row'] }}</td>
                                <td>{{ implode('; ', $error['messages']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <h5 class="card-header">Attendees</h5>
        <div class="card-body pb-0">
            <form method="GET" action="/attendees" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input class="form-control" name="search" type="search" value="{{ $search }}" placeholder="Search by first or last name" aria-label="Search attendees by name">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                    @if ($search !== '')
                        <a class="btn btn-outline-secondary" href="/attendees">Clear</a>
                    @endif
                </div>
                <div class="col text-md-end text-muted">
                    @if ($attendees->total() === 0)
                        {{ $search === '' ? 'No attendees yet' : 'No attendees match' }}
                    @else
                        Showing {{ $attendees->firstItem() }}&#8211;{{ $attendees->lastItem() }} of {{ number_format($attendees->total()) }}
                    @endif
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Last name</th><th>First name</th><th>Middle name</th><th>Local Church</th><th>Circuit</th><th>Birthday</th><th>Baptismal</th><th>Type</th><th>Registration</th>@if ($canManage)<th></th>@endif</tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($attendees as $attendee)
                        <tr>
                            <td>{{ $attendee->last_name }}</td>
                            <td>
                                {{ $attendee->first_name }}
                                @if ($attendee->needs_reprint)
                                    <span class="badge bg-label-warning">Camp ID needs reprint</span>
                                @endif
                            </td>
                            <td>{{ $attendee->middle_name }}</td>
                            <td>{{ $attendee->localChurch->name }}</td>
                            <td>{{ $attendee->localChurch->circuit->name }}</td>
                            <td>{{ $attendee->birthday->format('Y-m-d') }}</td>
                            <td>{{ $attendee->is_baptized ? 'Yes' : 'No' }}</td>
                            <td>{{ ucfirst($attendee->type->value) }}</td>
                            <td>{{ str($attendee->registration_type->value)->replace('_', ' ')->title() }}</td>
                            @if ($canManage)
                                <td class="text-nowrap">
                                    <form method="POST" action="/attendees/{{ $attendee->id }}" class="d-inline-flex gap-2" onsubmit="return confirm('Remove this attendee? Their Camp ID will stop working.')">
                                        @csrf
                                        @method('DELETE')
                                        <a class="btn btn-sm btn-outline-primary" href="/attendees/{{ $attendee->id }}/edit">Edit</a>
                                        <a class="btn btn-sm btn-outline-secondary" target="_blank" href="/attendees/{{ $attendee->id }}/camp-id">Camp ID</a>
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($attendees->hasPages())
            <div class="card-body pb-0">
                {{ $attendees->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
