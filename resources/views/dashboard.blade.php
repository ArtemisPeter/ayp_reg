@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="duyog-card p-4 p-lg-5 mb-4">
        <p class="duyog-eyebrow mb-2">DUYOG '26 · ROOTED</p>
        <h1 class="duyog-heading mb-3" style="font-size: clamp(2.2rem, 5vw, 4rem);">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mb-0">Signed in as {{ str(auth()->user()->role->value)->replace('_', ' ')->title() }}@if (auth()->user()->circuit) for {{ auth()->user()->circuit->name }}@endif.</p>
    </div>

    @if (isset($overview))
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <div class="duyog-card h-100 p-4 text-center">
                    <p class="duyog-eyebrow mb-1">Registered</p>
                    <p class="duyog-heading mb-0" style="font-size: 2.5rem;">{{ $overview['total'] }}</p>
                    <p class="mb-0 small">{{ $overview['youth'] }} youth &middot; {{ $overview['pastors'] }} pastors</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="duyog-card h-100 p-4 text-center">
                    <p class="duyog-eyebrow mb-1">Pre-Registration</p>
                    <p class="duyog-heading mb-0" style="font-size: 2.5rem;">{{ $overview['preRegistration'] }}</p>
                    <p class="mb-0 small">vs {{ $overview['onSite'] }} on-site</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="duyog-card h-100 p-4 text-center">
                    <p class="duyog-eyebrow mb-1">Baptized</p>
                    <p class="duyog-heading mb-0" style="font-size: 2.5rem;">{{ $overview['baptized'] }}</p>
                    <p class="mb-0 small">{{ $overview['notBaptized'] }} not yet baptized</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="duyog-card h-100 p-4 text-center">
                    <p class="duyog-eyebrow mb-1">On-Site</p>
                    <p class="duyog-heading mb-0" style="font-size: 2.5rem;">{{ $overview['onSite'] }}</p>
                    <p class="mb-0 small">walk-in registrations</p>
                </div>
            </div>
        </div>
    @endif

    @if (isset($perCircuit))
        <div class="duyog-card p-4 mb-4">
            <h2 class="h5 mb-3">Registration by Circuit</h2>
            <div class="table-responsive">
                <table class="table duyog-table mb-0">
                    <thead>
                        <tr>
                            <th>Circuit</th>
                            <th class="text-end">Registered</th>
                            <th class="text-end">Pre-Reg</th>
                            <th class="text-end">On-Site</th>
                            <th class="text-end">IDs Printed</th>
                            <th class="text-end">IDs Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perCircuit as $row)
                            <tr>
                                <td>{{ $row['circuit'] }}</td>
                                <td class="text-end">{{ $row['total'] }}</td>
                                <td class="text-end">{{ $row['preRegistration'] }}</td>
                                <td class="text-end">{{ $row['onSite'] }}</td>
                                <td class="text-end">{{ $row['printed'] }}</td>
                                <td class="text-end">{{ $row['pending'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="row g-4">
        @if (in_array(auth()->user()->role, [\App\Enums\Role::CircuitPresident, \App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
            <div class="col-md-6 col-xl-4"><a href="/attendees" class="duyog-card d-block h-100 p-4 text-decoration-none"><span class="bx bx-group fs-2 duyog-icon-accent"></span><h2 class="h5 mt-3">Attendee roster</h2><p class="mb-0">Review, update, and manage camp registrations.</p></a></div>
        @endif
        @if (in_array(auth()->user()->role, [\App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
            <div class="col-md-6 col-xl-4"><a href="/camp-ids" class="duyog-card d-block h-100 p-4 text-decoration-none"><span class="bx bx-id-card fs-2 duyog-icon-accent"></span><h2 class="h5 mt-3">Camp IDs</h2><p class="mb-0">Prepare and print attendee identification cards.</p></a></div>
        @endif
        @if (in_array(auth()->user()->role, [\App\Enums\Role::Security, \App\Enums\Role::Admin], true))
            <div class="col-md-6 col-xl-4"><a href="/gate" class="duyog-card d-block h-100 p-4 text-decoration-none"><span class="bx bx-scan fs-2 duyog-icon-accent"></span><h2 class="h5 mt-3">Gate scanner</h2><p class="mb-0">Check attendees in and out at camp.</p></a></div>
        @endif
        @if (in_array(auth()->user()->role, [\App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
            <div class="col-md-6 col-xl-4"><a href="/gate/review" class="duyog-card d-block h-100 p-4 text-decoration-none"><span class="bx bx-flag fs-2 duyog-icon-accent"></span><h2 class="h5 mt-3">Gate scan review</h2><p class="mb-0">Review flagged check-in and check-out scans.</p></a></div>
        @endif
        @if (in_array(auth()->user()->role, [\App\Enums\Role::Tabulator, \App\Enums\Role::Admin], true))
            <div class="col-md-6 col-xl-4"><a href="/events" class="duyog-card d-block h-100 p-4 text-decoration-none"><span class="bx bx-trophy fs-2 duyog-icon-accent"></span><h2 class="h5 mt-3">Events &amp; scores</h2><p class="mb-0">Record event scores and follow circuit standings.</p></a></div>
        @endif
        @if (auth()->user()->role === \App\Enums\Role::Admin)
            <div class="col-md-6 col-xl-4"><a href="/admin/youth-camps" class="duyog-card d-block h-100 p-4 text-decoration-none"><span class="bx bx-cog fs-2 duyog-icon-accent"></span><h2 class="h5 mt-3">Camp administration</h2><p class="mb-0">Manage camp details, staff, circuits, and churches.</p></a></div>
        @endif
    </div>
@endsection

