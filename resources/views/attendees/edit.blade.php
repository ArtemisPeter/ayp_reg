@extends('layouts.app')

@section('title', 'Edit attendee')

@section('content')
    <p><a href="/attendees"><i class="bx bx-chevron-left"></i> All Attendees</a></p>

    <div class="card">
        <h5 class="card-header">Edit {{ $attendee->first_name }} {{ $attendee->last_name }}</h5>
        <div class="card-body">
            @if ($attendee->needs_reprint)
                <div class="alert alert-warning">This person's Camp ID needs reprint.</div>
            @endif
            <p class="text-muted">Changing the first name marks the Camp ID for reprint, because the card shows it.</p>
            <form method="POST" action="/attendees/{{ $attendee->id }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label" for="first_name">First name</label>
                    <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $attendee->first_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="middle_name">Middle name (optional)</label>
                    <input class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name', $attendee->middle_name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="last_name">Last name</label>
                    <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $attendee->last_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="local_church_id">Local Church</label>
                    <select class="form-select" id="local_church_id" name="local_church_id" required>
                        @foreach ($churches as $church)
                            <option value="{{ $church->id }}" @selected((int) old('local_church_id', $attendee->local_church_id) === $church->id)>{{ $church->circuit->name }}: {{ $church->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="birthday">Birthday</label>
                    <input class="form-control" id="birthday" name="birthday" type="date" value="{{ old('birthday', $attendee->birthday->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="baptismal">Baptismal</label>
                    <select class="form-select" id="baptismal" name="baptismal" required>
                        @php($baptismal = old('baptismal', $attendee->is_baptized ? 'Yes' : 'No'))
                        <option value="Yes" @selected($baptismal === 'Yes')>Yes</option>
                        <option value="No" @selected($baptismal === 'No')>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" for="type">Type</label>
                    <select class="form-select" id="type" name="type" required>
                        @php($type = old('type', $attendee->type->value === 'pastor' ? 'P' : 'Y'))
                        <option value="Y" @selected($type === 'Y')>Youth</option>
                        <option value="P" @selected($type === 'P')>Pastor</option>
                    </select>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
