@extends('layouts.app')

@section('title', 'Youth Camps')

@section('content')
    @include('partials.page-head', [
        'icon' => 'bx-calendar-event',
        'title' => 'Youth Camps',
        'text' => 'The current camp is the one shown on the public site and used for registration.',
    ])

    <div class="card mb-4">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-plus-circle" aria-hidden="true"></i> Add a Youth Camp</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/youth-camps" class="row g-3">
                @csrf
                <div class="col-sm-4 col-md-2">
                    <label class="form-label" for="year">Year</label>
                    <input class="form-control" id="year" name="year" type="number" value="{{ old('year', now()->year) }}" required>
                </div>
                <div class="col-sm-8 col-md-4">
                    <label class="form-label" for="theme">Theme</label>
                    <input class="form-control" id="theme" name="theme" value="{{ old('theme') }}" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="starts_on">Starts</label>
                    <input class="form-control" id="starts_on" name="starts_on" type="date" value="{{ old('starts_on') }}" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="ends_on">Ends</label>
                    <input class="form-control" id="ends_on" name="ends_on" type="date" value="{{ old('ends_on') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="venue">Venue</label>
                    <input class="form-control" id="venue" name="venue" value="{{ old('venue') }}" placeholder="e.g. Camp Mariano, Koronadal City">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label" for="verse_reference">Verse reference</label>
                    <input class="form-control" id="verse_reference" name="verse_reference" value="{{ old('verse_reference') }}" placeholder="e.g. Colossians 2:6-7">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label" for="tagline">Tagline</label>
                    <input class="form-control" id="tagline" name="tagline" value="{{ old('tagline') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="pre_registration_fee">Pre-registration fee (&#8369;)</label>
                    <input class="form-control" id="pre_registration_fee" name="pre_registration_fee" type="number" step="0.01" min="0" value="{{ old('pre_registration_fee') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="on_site_fee">On-site fee (&#8369;)</label>
                    <input class="form-control" id="on_site_fee" name="on_site_fee" type="number" step="0.01" min="0" value="{{ old('on_site_fee') }}">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-plus" aria-hidden="true"></i> Add camp</button>
                </div>
            </form>
        </div>
    </div>

    <div class="duyog-section-heading mb-3">Youth Camps</div>

    @if ($youthCamps->isEmpty())
        <div class="card">
            <div class="duyog-empty">
                <i class="bx bx-calendar-event" aria-hidden="true"></i>
                <strong>No Youth Camps yet</strong>
                <span>Add the first camp with the form above, then make it current.</span>
            </div>
        </div>
    @endif

    <div class="duyog-stack">
        @foreach ($youthCamps as $camp)
            <div class="card duyog-camp-card">
                <div class="card-header duyog-panel-head">
                    <h2><i class="bx bx-calendar" aria-hidden="true"></i> {{ $camp->year }} <small>{{ $camp->theme }}</small></h2>
                    <div class="duyog-row-actions">
                        @if ($camp->is_current)
                            <span class="badge bg-label-success"><i class="bx bx-check" aria-hidden="true"></i> Current camp</span>
                        @else
                            <form method="POST" action="/admin/youth-camps/{{ $camp->id }}/current" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bx bx-star" aria-hidden="true"></i> Make current</button>
                            </form>
                            <form method="POST" action="/admin/youth-camps/{{ $camp->id }}" class="d-inline" onsubmit="return confirm('Remove the {{ $camp->year }} Youth Camp?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bx bx-trash" aria-hidden="true"></i> Remove</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/youth-camps/{{ $camp->id }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-sm-4 col-md-2">
                            <label class="form-label" for="camp-{{ $camp->id }}-year">Year</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-year" name="year" type="number" value="{{ $camp->year }}">
                        </div>
                        <div class="col-sm-8 col-md-4">
                            <label class="form-label" for="camp-{{ $camp->id }}-theme">Theme</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-theme" name="theme" value="{{ $camp->theme }}" placeholder="Theme">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="camp-{{ $camp->id }}-starts">Starts</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-starts" name="starts_on" type="date" value="{{ $camp->starts_on->format('Y-m-d') }}">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="camp-{{ $camp->id }}-ends">Ends</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-ends" name="ends_on" type="date" value="{{ $camp->ends_on->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="camp-{{ $camp->id }}-venue">Venue</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-venue" name="venue" value="{{ $camp->venue }}" placeholder="Venue">
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label" for="camp-{{ $camp->id }}-verse">Verse reference</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-verse" name="verse_reference" value="{{ $camp->verse_reference }}" placeholder="Verse reference">
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label" for="camp-{{ $camp->id }}-tagline">Tagline</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-tagline" name="tagline" value="{{ $camp->tagline }}" placeholder="Tagline">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="camp-{{ $camp->id }}-pre-fee">Pre-registration fee (&#8369;)</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-pre-fee" name="pre_registration_fee" type="number" step="0.01" min="0" value="{{ $camp->pre_registration_fee }}" placeholder="Pre-reg fee">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label" for="camp-{{ $camp->id }}-site-fee">On-site fee (&#8369;)</label>
                            <input class="form-control form-control-sm" id="camp-{{ $camp->id }}-site-fee" name="on_site_fee" type="number" step="0.01" min="0" value="{{ $camp->on_site_fee }}" placeholder="On-site fee">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bx bx-check" aria-hidden="true"></i> Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
