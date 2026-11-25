@extends('layouts.app')

@section('title', 'Attendees')

@section('content')
    @php($canManage = in_array(auth()->user()->role, [\App\Enums\Role::Secretary, \App\Enums\Role::Admin], true))
    @php($canUpload = $canManage || auth()->user()->role === \App\Enums\Role::CircuitPresident)
    @php($preRegistrationOpen = $camp?->isPreRegistrationOpen() ?? true)
    @php($deadlineLabel = $camp?->pre_registration_deadline?->format('F j, Y'))

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

    @if ($canManage && $camp)
        <div class="card mb-4">
            <div class="card-header duyog-panel-head">
                <h2><i class="bx bx-calendar-x" aria-hidden="true"></i> Pre-registration deadline</h2>
                <span class="badge {{ $preRegistrationOpen ? 'bg-label-success' : 'bg-label-danger' }}">{{ $preRegistrationOpen ? 'Open' : 'Closed' }}</span>
            </div>
            <div class="card-body">
                <p class="duyog-panel-note">
                    @if ($deadlineLabel === null)
                        No deadline is set, so Circuit Presidents can upload pre-registration rosters at any time.
                    @elseif ($preRegistrationOpen)
                        Circuit Presidents can upload until the end of {{ $deadlineLabel }}.
                    @else
                        Closed since the end of {{ $deadlineLabel }}. Circuit Presidents can no longer upload; set a later date to reopen it.
                    @endif
                </p>
                <form method="POST" action="/pre-registration-deadline" class="row g-3 align-items-end">
                    @csrf
                    @method('PUT')
                    <div class="col-sm-6 col-md-4">
                        <label class="form-label" for="pre_registration_deadline">Last day to upload</label>
                        <input class="form-control" id="pre_registration_deadline" name="pre_registration_deadline" type="date" value="{{ old('pre_registration_deadline', $camp->pre_registration_deadline?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-sm-6 col-md-8 duyog-form-actions">
                        <button class="btn btn-primary" type="submit"><i class="bx bx-check" aria-hidden="true"></i> Save deadline</button>
                        @if ($deadlineLabel !== null)
                            <button class="btn btn-outline-secondary" type="submit" name="pre_registration_deadline" value="">Remove deadline</button>
                        @endif
                    </div>
                </form>
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
                    @php($isAdmin = auth()->user()->role === \App\Enums\Role::Admin)
                    <h2><i class="bx bx-upload" aria-hidden="true"></i> {{ $isAdmin ? 'Upload a roster' : ($canManage ? 'Upload an on-site roster' : 'Upload a roster') }}</h2>
                </div>
                <div class="card-body">
                    @if ($isAdmin)
                        <p class="duyog-panel-note">Upload a CSV for any Circuit. Each person is registered under the Circuit and Local Church named in the file. If any row has a problem, nothing is registered and you will see every problem below.</p>
                    @elseif ($canManage)
                        <p class="duyog-panel-note">Upload a CSV of people registered on-site, for example after an internet outage. It can cover any Circuit. If any row has a problem, nothing is registered and you will see every problem below.</p>
                    @elseif (! $preRegistrationOpen)
                        <div class="alert alert-warning d-flex align-items-center gap-2" role="status"><i class="bx bx-lock-alt" aria-hidden="true"></i> Pre-registration closed on {{ $deadlineLabel }}. Uploading is turned off; ask the Secretary to register anyone else on-site.</div>
                    @else
                        <p class="duyog-panel-note">Upload the CSV your Local Church Presidents filled in, after payment is confirmed. If any row has a problem, nothing is registered and you will see every problem below.@if ($deadlineLabel) <strong>Pre-registration closes at the end of {{ $deadlineLabel }}.</strong>@endif</p>
                    @endif
                    @php($uploadLocked = ! $canManage && ! $preRegistrationOpen)
                    <form method="POST" action="/registrations/upload" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="file">CSV file</label>
                            <input class="form-control" id="file" name="file" type="file" accept=".csv,text/csv" required @disabled($uploadLocked)>
                        </div>
                        @if ($isAdmin)
                            <fieldset class="col-12">
                                <legend class="form-label fs-6">Register them as</legend>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="registration_type" id="registration_type_pre" value="pre_registration" @checked(old('registration_type', 'pre_registration') === 'pre_registration')>
                                    <label class="form-check-label" for="registration_type_pre">Pre-registration</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="registration_type" id="registration_type_onsite" value="on_site" @checked(old('registration_type') === 'on_site')>
                                    <label class="form-check-label" for="registration_type_onsite">On-site</label>
                                </div>
                            </fieldset>
                        @endif
                        <div class="col-12 duyog-form-actions">
                            <button class="btn btn-primary" type="submit" @disabled($uploadLocked)><i class="bx bx-upload" aria-hidden="true"></i> Upload</button>
                            <a class="btn btn-outline-secondary" href="/template.csv"><i class="bx bx-download" aria-hidden="true"></i> CSV template</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        @php($exportQuery = request()->getQueryString() ? '?'.request()->getQueryString() : '')
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-list-ul" aria-hidden="true"></i> Roster</h2>
            <div class="duyog-panel-actions">
                <a class="btn btn-sm btn-outline-primary" id="roster_export_csv" data-base="/attendees/export/csv" href="/attendees/export/csv{{ $exportQuery }}"><i class="bx bx-spreadsheet" aria-hidden="true"></i> Export CSV</a>
                <a class="btn btn-sm btn-outline-primary" id="roster_export_pdf" data-base="/attendees/export/pdf" href="/attendees/export/pdf{{ $exportQuery }}" target="_blank" rel="noopener"><i class="bx bxs-file-pdf" aria-hidden="true"></i> Export PDF</a>
            </div>
        </div>
        <form method="GET" action="/attendees" role="search" id="roster_search_form">
            <div class="duyog-toolbar">
                <div class="duyog-search">
                    <i class="bx bx-search" aria-hidden="true"></i>
                    <input class="form-control" id="roster_search" name="search" type="search" value="{{ $search }}" placeholder="Search names, churches, birthdays…" title="Matches any column: name, church, Circuit, birthday, Yes/No, Youth/Pastor, Pre-Reg/On-Site" aria-label="Search attendees by any column" aria-controls="roster_results" autocomplete="off">
                </div>
                <button class="btn btn-outline-primary" type="submit">Search</button>
                <a class="btn btn-outline-secondary" id="roster_search_clear" href="/attendees" @unless ($hasFilters) hidden @endunless>Clear all</a>
                <span class="duyog-panel-meta" id="roster_meta" aria-live="polite">
                    @if ($attendees->total() === 0)
                        {{ $hasFilters ? 'No attendees match' : 'No attendees yet' }}
                    @else
                        Showing {{ $attendees->firstItem() }}&#8211;{{ $attendees->lastItem() }} of {{ number_format($attendees->total()) }}
                    @endif
                </span>
            </div>
            <div class="duyog-roster-filters" role="group" aria-label="Filter the roster">
                @if ($filterCircuits->count() > 1)
                    <div>
                        <label for="filter_circuit">Circuit</label>
                        <select class="form-select form-select-sm" id="filter_circuit" name="circuit">
                            <option value="">All Circuits</option>
                            @foreach ($filterCircuits as $circuit)
                                <option value="{{ $circuit->id }}" @selected($filters['circuit'] === $circuit->id)>{{ $circuit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label for="filter_church">Local Church</label>
                    <select class="form-select form-select-sm" id="filter_church" name="church">
                        <option value="">All churches</option>
                        @foreach ($filterChurches as $church)
                            <option value="{{ $church->id }}" data-circuit="{{ $church->circuit_id }}" @selected($filters['church'] === $church->id)>{{ $church->name }}@if ($filterCircuits->count() > 1) ({{ $church->circuit->name }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="filter_baptismal">Baptismal</label>
                    <select class="form-select form-select-sm" id="filter_baptismal" name="baptismal">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['baptismal'] === 'yes')>Yes</option>
                        <option value="no" @selected($filters['baptismal'] === 'no')>No</option>
                    </select>
                </div>
                <div>
                    <label for="filter_type">Type</label>
                    <select class="form-select form-select-sm" id="filter_type" name="type">
                        <option value="">Any</option>
                        <option value="youth" @selected($filters['type'] === 'youth')>Youth</option>
                        <option value="pastor" @selected($filters['type'] === 'pastor')>Pastor</option>
                    </select>
                </div>
                <div>
                    <label for="filter_registration">Registration</label>
                    <select class="form-select form-select-sm" id="filter_registration" name="registration">
                        <option value="">Any</option>
                        <option value="pre_registration" @selected($filters['registration'] === 'pre_registration')>Pre-Registration</option>
                        <option value="on_site" @selected($filters['registration'] === 'on_site')>On-Site</option>
                    </select>
                </div>
            </div>
        </form>

        <div id="roster_results">
        @if ($attendees->total() === 0)
            <div class="duyog-empty">
                <i class="bx {{ $hasFilters ? 'bx-search-alt' : 'bx-group' }}" aria-hidden="true"></i>
                <strong>{{ $hasFilters ? 'No one matches that search and filters' : 'Nobody is registered yet' }}</strong>
                <span>{{ $hasFilters ? 'Check the spelling, loosen a filter, or Clear all to see everyone. Every word must match.' : 'Attendees appear here once a roster is uploaded.' }}</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr><th scope="col">Name</th><th scope="col">Local Church</th><th scope="col">Birthday</th><th scope="col">Baptismal</th><th scope="col">Type</th><th scope="col">Registration</th>@if ($canUpload)<th scope="col"><span class="visually-hidden">Actions</span></th>@endif</tr>
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
                                <td class="text-nowrap">{{ $attendee->registration_type === \App\Enums\RegistrationType::PreRegistration ? 'Pre-Registration' : 'On-Site' }}</td>
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
                                @elseif ($canUpload)
                                    <td class="text-nowrap text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="/attendees/{{ $attendee->id }}/edit"><i class="bx bx-edit-alt" aria-hidden="true"></i> Edit</a>
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
    </div>

    <script>
        (() => {
            const form = document.getElementById('roster_search_form');
            const input = document.getElementById('roster_search');
            const clear = document.getElementById('roster_search_clear');
            const meta = document.getElementById('roster_meta');
            const results = document.getElementById('roster_results');
            const circuit = document.getElementById('filter_circuit');
            const church = document.getElementById('filter_church');
            const exports = [document.getElementById('roster_export_csv'), document.getElementById('roster_export_pdf')];
            let timer = null;
            let pending = null;

            // With a Circuit chosen, the church list shows only that Circuit's churches.
            const limitChurches = () => {
                if (! circuit) {
                    return;
                }
                for (const option of church.options) {
                    option.hidden = option.value !== '' && circuit.value !== '' && option.dataset.circuit !== circuit.value;
                }
                if (church.selectedOptions[0]?.hidden) {
                    church.value = '';
                }
            };

            const currentUrl = () => {
                const url = new URL(form.action, window.location.origin);
                for (const [name, value] of new FormData(form)) {
                    if (String(value).trim() !== '') {
                        url.searchParams.set(name, String(value).trim());
                    }
                }
                return url;
            };

            const load = async () => {
                const url = currentUrl();

                pending?.abort();
                pending = new AbortController();
                results.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url, { signal: pending.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (! response.ok) {
                        throw new Error(response.statusText);
                    }

                    const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                    results.innerHTML = page.getElementById('roster_results').innerHTML;
                    meta.textContent = page.getElementById('roster_meta').textContent.trim();
                    clear.hidden = url.search === '';
                    exports.forEach((link) => link.href = link.dataset.base + url.search);
                    history.replaceState(null, '', url);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        form.submit();
                    }
                } finally {
                    results.removeAttribute('aria-busy');
                }
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(load, 300);
            });

            form.querySelectorAll('select').forEach((field) => field.addEventListener('change', () => {
                if (field === circuit) {
                    limitChurches();
                }
                clearTimeout(timer);
                load();
            }));

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                clearTimeout(timer);
                load();
            });

            clear.addEventListener('click', (event) => {
                event.preventDefault();
                form.reset();
                form.querySelectorAll('input, select').forEach((field) => field.value = '');
                limitChurches();
                load();
                input.focus();
            });

            limitChurches();
        })();
    </script>
@endsection
