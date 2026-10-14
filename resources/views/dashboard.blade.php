@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @use('App\Enums\Role')

    @php
        $user = auth()->user();
        $role = $user->role;
        $percent = fn (int $part, int $whole): int => $whole > 0 ? (int) round($part / $whole * 100) : 0;

        $shortcuts = collect([
            ['roles' => [Role::CircuitPresident, Role::Secretary, Role::Admin], 'href' => '/attendees', 'icon' => 'bx-group', 'title' => 'Attendee roster', 'text' => 'Review, update, and manage camp registrations.'],
            ['roles' => [Role::Secretary, Role::Admin], 'href' => '/camp-ids', 'icon' => 'bx-id-card', 'title' => 'Camp IDs', 'text' => 'Prepare and print attendee identification cards.'],
            ['roles' => [Role::Security, Role::Admin], 'href' => '/gate', 'icon' => 'bx-scan', 'title' => 'Gate scanner', 'text' => 'Check attendees in and out at camp.'],
            ['roles' => [Role::Secretary, Role::Admin], 'href' => '/gate/review', 'icon' => 'bx-flag', 'title' => 'Gate scan review', 'text' => 'Review flagged check-in and check-out scans.'],
            ['roles' => [Role::Tabulator, Role::Admin], 'href' => '/events', 'icon' => 'bx-trophy', 'title' => 'Events & scores', 'text' => 'Record event scores and follow circuit standings.'],
            ['roles' => [Role::Admin], 'href' => '/admin/youth-camps', 'icon' => 'bx-cog', 'title' => 'Camp administration', 'text' => 'Manage camp details, staff, circuits, and churches.'],
        ])->filter(fn (array $shortcut) => in_array($role, $shortcut['roles'], true));
    @endphp

    <header class="duyog-dash-welcome">
        <div>
            <p class="duyog-eyebrow mb-2">DUYOG '26 · ROOTED</p>
            <h1>Welcome, {{ $user->name }}</h1>
            <p class="duyog-dash-chips">
                <span class="duyog-dash-chip"><i class="bx bx-shield-quarter" aria-hidden="true"></i> {{ str($role->value)->replace('_', ' ')->title() }}</span>
                @if ($user->circuit)
                    <span class="duyog-dash-chip"><i class="bx bx-map-pin" aria-hidden="true"></i> {{ $user->circuit->name }}</span>
                @endif
            </p>
        </div>
        <img src="{{ asset('images/duyog-emblem.webp') }}" alt="" width="88" height="88" class="duyog-dash-emblem">
    </header>

    @if (isset($overview))
        @php
            $idsPrinted = isset($perCircuit) ? $perCircuit->sum('printed') : null;
            $idsPending = isset($perCircuit) ? $perCircuit->sum('pending') : null;
        @endphp

        <section class="mb-4" aria-labelledby="overview-title">
            <div class="duyog-dash-section-heading">
                <h2 id="overview-title">{{ isset($perCircuit) ? 'District registration' : 'Your circuit\'s registration' }}</h2>
                <span>Current camp</span>
            </div>

            @if ($overview['total'] === 0)
                <p class="duyog-dash-empty"><i class="bx bx-info-circle" aria-hidden="true"></i> No one is registered for the current camp yet. Totals appear here as rosters are uploaded.</p>
            @endif

            <div class="duyog-dash-stats">
                <article class="duyog-dash-stat is-primary">
                    <div class="duyog-dash-stat-head"><span class="duyog-dash-stat-icon"><i class="bx bx-group" aria-hidden="true"></i></span><h3>Registered</h3></div>
                    <p class="duyog-dash-stat-value">{{ number_format($overview['total']) }}</p>
                    <div class="duyog-dash-bar" role="img" aria-label="{{ $overview['youth'] }} youth and {{ $overview['pastors'] }} pastors"><span style="width: {{ $percent($overview['youth'], $overview['total']) }}%"></span></div>
                    <p class="duyog-dash-stat-note">{{ number_format($overview['youth']) }} youth &middot; {{ number_format($overview['pastors']) }} {{ Str::plural('pastor', $overview['pastors']) }}</p>
                </article>
                <article class="duyog-dash-stat">
                    <div class="duyog-dash-stat-head"><span class="duyog-dash-stat-icon"><i class="bx bx-calendar-check" aria-hidden="true"></i></span><h3>Pre-registration</h3></div>
                    <p class="duyog-dash-stat-value">{{ number_format($overview['preRegistration']) }} <small>{{ $percent($overview['preRegistration'], $overview['total']) }}%</small></p>
                    <div class="duyog-dash-bar" role="img" aria-label="{{ $overview['preRegistration'] }} pre-registered and {{ $overview['onSite'] }} on-site"><span style="width: {{ $percent($overview['preRegistration'], $overview['total']) }}%"></span></div>
                    <p class="duyog-dash-stat-note">vs {{ number_format($overview['onSite']) }} on-site</p>
                </article>
                <article class="duyog-dash-stat">
                    <div class="duyog-dash-stat-head"><span class="duyog-dash-stat-icon"><i class="bx bx-water" aria-hidden="true"></i></span><h3>Baptized</h3></div>
                    <p class="duyog-dash-stat-value">{{ number_format($overview['baptized']) }} <small>{{ $percent($overview['baptized'], $overview['total']) }}%</small></p>
                    <div class="duyog-dash-bar" role="img" aria-label="{{ $overview['baptized'] }} baptized and {{ $overview['notBaptized'] }} not yet baptized"><span style="width: {{ $percent($overview['baptized'], $overview['total']) }}%"></span></div>
                    <p class="duyog-dash-stat-note">{{ number_format($overview['notBaptized']) }} not yet baptized</p>
                </article>
                @if ($idsPrinted !== null)
                    <article class="duyog-dash-stat">
                        <div class="duyog-dash-stat-head"><span class="duyog-dash-stat-icon"><i class="bx bx-id-card" aria-hidden="true"></i></span><h3>Camp IDs printed</h3></div>
                        <p class="duyog-dash-stat-value">{{ number_format($idsPrinted) }} <small>{{ $percent($idsPrinted, $idsPrinted + $idsPending) }}%</small></p>
                        <div class="duyog-dash-bar" role="img" aria-label="{{ $idsPrinted }} Camp IDs printed and {{ $idsPending }} pending"><span style="width: {{ $percent($idsPrinted, $idsPrinted + $idsPending) }}%"></span></div>
                        <p class="duyog-dash-stat-note">{{ number_format($idsPending) }} still to print</p>
                    </article>
                @else
                    <article class="duyog-dash-stat">
                        <div class="duyog-dash-stat-head"><span class="duyog-dash-stat-icon"><i class="bx bx-walk" aria-hidden="true"></i></span><h3>On-site</h3></div>
                        <p class="duyog-dash-stat-value">{{ number_format($overview['onSite']) }} <small>{{ $percent($overview['onSite'], $overview['total']) }}%</small></p>
                        <div class="duyog-dash-bar" role="img" aria-label="{{ $overview['onSite'] }} of {{ $overview['total'] }} registered on-site"><span style="width: {{ $percent($overview['onSite'], $overview['total']) }}%"></span></div>
                        <p class="duyog-dash-stat-note">walk-in registrations</p>
                    </article>
                @endif
            </div>
        </section>
    @endif

    @if (isset($perCircuit))
        @php($largestCircuit = max(1, (int) $perCircuit->max('total')))

        <section class="duyog-card duyog-dash-panel mb-4" aria-labelledby="per-circuit-title">
            <div class="duyog-dash-panel-head">
                <h2 id="per-circuit-title">Registration by Circuit</h2>
                <span>{{ $perCircuit->count() }} {{ $perCircuit->count() === 1 ? 'circuit' : 'circuits' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table duyog-table duyog-dash-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Circuit</th>
                            <th scope="col">Registered</th>
                            <th scope="col" class="text-end">Pre-Reg</th>
                            <th scope="col" class="text-end">On-Site</th>
                            <th scope="col">Camp IDs printed</th>
                            <th scope="col" class="text-end">IDs Pending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perCircuit as $row)
                            <tr>
                                <th scope="row">{{ $row['circuit'] }}</th>
                                <td>
                                    <div class="duyog-dash-meter"><strong>{{ number_format($row['total']) }}</strong><span class="duyog-dash-bar" aria-hidden="true"><span style="width: {{ $percent($row['total'], $largestCircuit) }}%"></span></span></div>
                                </td>
                                <td class="text-end">{{ number_format($row['preRegistration']) }}</td>
                                <td class="text-end">{{ number_format($row['onSite']) }}</td>
                                <td>
                                    <div class="duyog-dash-meter"><strong>{{ number_format($row['printed']) }}<small> / {{ number_format($row['total']) }}</small></strong><span class="duyog-dash-bar is-success" aria-hidden="true"><span style="width: {{ $percent($row['printed'], $row['total']) }}%"></span></span></div>
                                </td>
                                <td class="text-end">
                                    @if ($row['pending'] > 0)
                                        <span class="badge bg-label-warning">{{ number_format($row['pending']) }} pending</span>
                                    @elseif ($row['total'] > 0)
                                        <span class="badge bg-label-success"><i class="bx bx-check" aria-hidden="true"></i> Done</span>
                                    @else
                                        <span class="duyog-dash-muted">–</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if ($perCircuit->count() > 1)
                        <tfoot>
                            <tr>
                                <th scope="row">All circuits</th>
                                <td><strong>{{ number_format($perCircuit->sum('total')) }}</strong></td>
                                <td class="text-end">{{ number_format($perCircuit->sum('preRegistration')) }}</td>
                                <td class="text-end">{{ number_format($perCircuit->sum('onSite')) }}</td>
                                <td><strong>{{ number_format($idsPrinted) }}<small> / {{ number_format($perCircuit->sum('total')) }}</small></strong></td>
                                <td class="text-end">{{ number_format($idsPending) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </section>
    @endif

    <section aria-labelledby="tools-title">
        <div class="duyog-dash-section-heading">
            <h2 id="tools-title">Your tools</h2>
        </div>
        <div class="duyog-dash-tools">
            @foreach ($shortcuts as $shortcut)
                <a href="{{ $shortcut['href'] }}" class="duyog-dash-tool">
                    <span class="duyog-dash-tool-icon"><i class="bx {{ $shortcut['icon'] }}" aria-hidden="true"></i></span>
                    <span class="duyog-dash-tool-copy"><strong>{{ $shortcut['title'] }}</strong><span>{{ $shortcut['text'] }}</span></span>
                    <i class="bx bx-right-arrow-alt duyog-dash-tool-arrow" aria-hidden="true"></i>
                </a>
            @endforeach
        </div>
    </section>
@endsection
