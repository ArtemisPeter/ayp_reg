@extends('layouts.app')

@section('title', 'Youth Camps')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">Add a Youth Camp</h5>
        <div class="card-body">
            <form method="POST" action="/admin/youth-camps" class="row g-3">
                @csrf
                <div class="col-md-2">
                    <label class="form-label" for="year">Year</label>
                    <input class="form-control" id="year" name="year" type="number" value="{{ old('year', now()->year) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="theme">Theme</label>
                    <input class="form-control" id="theme" name="theme" value="{{ old('theme') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="starts_on">Starts</label>
                    <input class="form-control" id="starts_on" name="starts_on" type="date" value="{{ old('starts_on') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="ends_on">Ends</label>
                    <input class="form-control" id="ends_on" name="ends_on" type="date" value="{{ old('ends_on') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="venue">Venue</label>
                    <input class="form-control" id="venue" name="venue" value="{{ old('venue') }}" placeholder="e.g. Camp Mariano, Koronadal City">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="verse_reference">Verse reference</label>
                    <input class="form-control" id="verse_reference" name="verse_reference" value="{{ old('verse_reference') }}" placeholder="e.g. Colossians 2:6-7">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="tagline">Tagline</label>
                    <input class="form-control" id="tagline" name="tagline" value="{{ old('tagline') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="pre_registration_fee">Pre-registration fee (&#8369;)</label>
                    <input class="form-control" id="pre_registration_fee" name="pre_registration_fee" type="number" step="0.01" min="0" value="{{ old('pre_registration_fee') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="on_site_fee">On-site fee (&#8369;)</label>
                    <input class="form-control" id="on_site_fee" name="on_site_fee" type="number" step="0.01" min="0" value="{{ old('on_site_fee') }}">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Add camp</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Youth Camps</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Camp details</th><th></th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($youthCamps as $camp)
                        <tr>
                            <td>
                                <form method="POST" action="/admin/youth-camps/{{ $camp->id }}" class="row g-2 align-items-center">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-auto"><input class="form-control form-control-sm" name="year" type="number" value="{{ $camp->year }}" style="width: 6rem"></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="theme" value="{{ $camp->theme }}" placeholder="Theme"></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="starts_on" type="date" value="{{ $camp->starts_on->format('Y-m-d') }}"></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="ends_on" type="date" value="{{ $camp->ends_on->format('Y-m-d') }}"></div>
                                    <div class="col-auto"><button class="btn btn-sm btn-outline-primary" type="submit">Save</button></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="venue" value="{{ $camp->venue }}" placeholder="Venue" style="min-width: 14rem"></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="verse_reference" value="{{ $camp->verse_reference }}" placeholder="Verse reference"></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="tagline" value="{{ $camp->tagline }}" placeholder="Tagline" style="min-width: 16rem"></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="pre_registration_fee" type="number" step="0.01" min="0" value="{{ $camp->pre_registration_fee }}" placeholder="Pre-reg fee" style="width: 8rem"></div>
                                    <div class="col-auto"><input class="form-control form-control-sm" name="on_site_fee" type="number" step="0.01" min="0" value="{{ $camp->on_site_fee }}" placeholder="On-site fee" style="width: 8rem"></div>
                                </form>
                            </td>
                            <td class="text-nowrap">
                                @if ($camp->is_current)
                                    <span class="badge bg-label-success">Current camp</span>
                                @else
                                    <form method="POST" action="/admin/youth-camps/{{ $camp->id }}/current" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Make current</button>
                                    </form>
                                    <form method="POST" action="/admin/youth-camps/{{ $camp->id }}" class="d-inline" onsubmit="return confirm('Remove the {{ $camp->year }} Youth Camp?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
