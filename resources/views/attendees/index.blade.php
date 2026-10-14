@extends('layouts.app')

@section('title', 'Attendees')

@section('content')
    @php($canManage = in_array(auth()->user()->role, [\App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
    @php($canUpload = $canManage || auth()->user()->role === \App\Enums\Role::CircuitPresident)

    @include('partials.page-head', [
        'icon' => 'bx-group',
        'title' => 'Attendees',
        'text' => $canManage ? 'Everyone registered for the current camp, across all Circuits.' : 'Everyone registered for the current camp from your Circuit.',
    ])

    @if (session('roster_errors'))
        <div class="card duyog-panel-danger mb-4" role="alert">
            <div class="card-header duyog-panel-head">
                <h2><i class="bx bx-error-circle" aria-hidden="true"></i> The file was not accepted. Nothing was registered.</h2>
                <span class="duyog-panel-meta">{{ count(session('roster_errors')) }} {{ Str::plural('row', count(session('roster_errors'))) }} to fix</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Row</th><th scope="col">Problem</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @foreach (session('roster_errors') as $error)
                            <tr>
                                <td class="duyog-cell-strong text-nowrap">Row {{ $error['row'] }}</td>
                                <td>{{ implode('; ', $error['messages']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($canUpload)
        <div class="{{ $canManage ? 'duyog-panel-grid' : '' }} mb-4">
            @if ($canManage)
                <div class="card">
                    <div class="card-header duyog-panel-head">
                        <h2><i class="bx bx-user-plus" aria-hidden="true"></i> Register someone on-site</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/attendees" class="row g-3">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label" for="first_name">First name</label>
                                <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" autocomplete="off" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="middle_name">Middle name (optional)</label>
                                <input class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name') }}" autocomplete="off">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="last_name">Last name</label>
                                <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" autocomplete="off" required>
                            </div>
                            @include('partials.church-picker', ['churches' => $churches, 'selectedChurchId' => old('local_church_id')])
                            <div class="col-sm-4">
                                <label class="form-label" for="birthday">Birthday</label>
                                <input class="form-control" id="birthday" name="birthday" type="date" value="{{ old('birthday') }}" required>
                            </div>
                            <div class="col-6 col-sm-4">
                                <label class="form-label" for="baptismal">Baptismal</label>
                                <select class="form-select" id="baptismal" name="baptismal" required>
                                    <option value="Yes" @selected(old('baptismal') === 'Yes')>Yes</option>
                                    <option value="No" @selected(old('baptismal') === 'No')>No</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-4">
                                <label class="form-label" for="type">Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="Y" @selected(old('type') === 'Y')>Youth</option>
                                    <option value="P" @selected(old('type') === 'P')>Pastor</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit"><i class="bx bx-user-plus" aria-hidden="true"></i> Register on-site</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header duyog-panel-head">
                    <h2><i class="bx bx-upload" aria-hidden="true"></i> {{ $canManage ? 'Upload an on-site roster' : 'Upload a roster' }}</h2>
                </div>
                <div class="card-body">
                    @if ($canManage)
                        <p class="duyog-panel-note">Upload a CSV of people registered on-site, for example after an internet outage. It can cover any Circuit. If any row has a problem, nothing is registered and you will see every problem below.</p>
                    @else
                        <p class="duyog-panel-note">Upload the CSV your Local Church Presidents filled in, after payment is confirmed. If any row has a problem, nothing is registered and you will see every problem below.</p>
                    @endif
                    <form method="POST" action="/registrations/upload" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="file">CSV file</label>
                            <input class="form-control" id="file" name="file" type="file" accept=".csv,text/csv" required>
                        </div>
                        <div class="col-12 duyog-form-actions">
                            <button class="btn btn-primary" type="submit"><i class="bx bx-upload" aria-hidden="true"></i> Upload</button>
                            <a class="btn btn-outline-secondary" href="/template.csv"><i class="bx bx-download" aria-hidden="true"></i> CSV template</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-list-ul" aria-hidden="true"></i> Roster</h2>
        </div>
        <form method="GET" action="/attendees" class="duyog-toolbar" role="search">
            <div class="duyog-search">
                <i class="bx bx-search" aria-hidden="true"></i>
                <input class="form-control" name="search" type="search" value="{{ $search }}" placeholder="Search by first or last name" aria-label="Search attendees by name">
            </div>
            <button class="btn btn-outline-primary" type="submit">Search</button>
            @if ($search !== '')
                <a class="btn btn-outline-secondary" href="/attendees">Clear</a>
            @endif
            <span class="duyog-panel-meta">
                @if ($attendees->total() === 0)
                    {{ $search === '' ? 'No attendees yet' : 'No attendees match' }}
                @else
                    Showing {{ $attendees->firstItem() }}&#8211;{{ $attendees->lastItem() }} of {{ number_format($attendees->total()) }}
                @endif
            </span>
        </form>

        @if ($attendees->total() === 0)
            <div class="duyog-empty">
                <i class="bx {{ $search === '' ? 'bx-group' : 'bx-search-alt' }}" aria-hidden="true"></i>
                <strong>{{ $search === '' ? 'Nobody is registered yet' : 'No one matches that name' }}</strong>
                <span>{{ $search === '' ? 'Attendees appear here once a roster is uploaded.' : 'Check the spelling, or clear the search to see everyone.' }}</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th scope="col">Name</th><th scope="col">Local Church</th><th scope="col">Birthday</th><th scope="col">Baptismal</th><th scope="col">Type</th><th scope="col">Registration</th>@if ($canManage)<th scope="col"><span class="visually-hidden">Actions</span></th>@endif</tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($attendees as $attendee)
                            <tr>
                                <td>
                                    <span class="duyog-cell-strong">{{ $attendee->last_name }}, {{ $attendee->first_name }}</span>
                                    @if ($attendee->middle_name)
                                        <span class="duyog-cell-sub">{{ $attendee->middle_name }}</span>
                                    @endif
                                    @if ($attendee->needs_reprint)
                                        <span class="badge bg-label-warning mt-1">Camp ID needs reprint</span>
                                    @endif
                                </td>
                                <td>{{ $attendee->localChurch->name }}<span class="duyog-cell-sub">{{ $attendee->localChurch->circuit->name }}</span></td>
                                <td class="text-nowrap">{{ $attendee->birthday->format('Y-m-d') }}</td>
                                <td><span class="badge {{ $attendee->is_baptized ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $attendee->is_baptized ? 'Yes' : 'No' }}</span></td>
                                <td><span class="badge {{ $attendee->type->value === 'pastor' ? 'bg-label-warning' : 'bg-label-primary' }}">{{ ucfirst($attendee->type->value) }}</span></td>
                                <td class="text-nowrap">{{ str($attendee->registration_type->value)->replace('_', ' ')->title() }}</td>
                                @if ($canManage)
                                    <td class="text-nowrap text-end">
                                        <form method="POST" action="/attendees/{{ $attendee->id }}" class="duyog-row-actions" onsubmit="return confirm('Remove this attendee? Their Camp ID will stop working.')">
                                            @csrf
                                            @method('DELETE')
                                            <a class="btn btn-sm btn-outline-primary" href="/attendees/{{ $attendee->id }}/edit"><i class="bx bx-edit-alt" aria-hidden="true"></i> Edit</a>
                                            <a class="btn btn-sm btn-outline-secondary" target="_blank" href="/attendees/{{ $attendee->id }}/camp-id"><i class="bx bx-id-card" aria-hidden="true"></i> Camp ID</a>
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bx bx-trash" aria-hidden="true"></i> Remove</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if ($attendees->hasPages())
            <div class="card-body pb-0">
                {{ $attendees->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
