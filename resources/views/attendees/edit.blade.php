@extends('layouts.app')

@section('title', 'Edit attendee')

@section('content')
    @include('partials.page-head', [
        'icon' => 'bx-edit-alt',
        'title' => 'Edit '.$attendee->first_name.' '.$attendee->last_name,
        'text' => 'Changing the first name marks the Camp ID for reprint, because the card shows it.',
        'back' => ['href' => '/attendees', 'label' => 'All Attendees'],
    ])

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-user" aria-hidden="true"></i> Attendee details</h2>
        </div>
        <div class="card-body">
            @if ($attendee->needs_reprint)
                <div class="alert alert-warning d-flex align-items-center gap-2" role="status"><i class="bx bx-printer" aria-hidden="true"></i> This person's Camp ID needs reprint.</div>
            @endif
            <form method="POST" action="/attendees/{{ $attendee->id }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label" for="first_name">First name</label>
                    <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $attendee->first_name) }}" autocomplete="off" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="middle_name">Middle name (optional)</label>
                    <input class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name', $attendee->middle_name) }}" autocomplete="off">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="last_name">Last name</label>
                    <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $attendee->last_name) }}" autocomplete="off" required>
                </div>
                @include('partials.church-picker', ['churches' => $churches, 'selectedChurchId' => old('local_church_id', $attendee->local_church_id)])
                <div class="col-sm-4">
                    <label class="form-label" for="birthday">Birthday</label>
                    <input class="form-control" id="birthday" name="birthday" type="date" value="{{ old('birthday', $attendee->birthday->format('Y-m-d')) }}" required>
                </div>
                <div class="col-6 col-sm-4">
                    <label class="form-label" for="baptismal">Baptismal</label>
                    <select class="form-select" id="baptismal" name="baptismal" required>
                        @php($baptismal = old('baptismal', $attendee->is_baptized ? 'Yes' : 'No'))
                        <option value="Yes" @selected($baptismal === 'Yes')>Yes</option>
                        <option value="No" @selected($baptismal === 'No')>No</option>
                    </select>
                </div>
                <div class="col-6 col-sm-4">
                    <label class="form-label" for="type">Type</label>
                    <select class="form-select" id="type" name="type" required>
                        @php($type = old('type', $attendee->type->value === 'pastor' ? 'P' : 'Y'))
                        <option value="Y" @selected($type === 'Y')>Youth</option>
                        <option value="P" @selected($type === 'P')>Pastor</option>
                    </select>
                </div>
                <div class="col-12 duyog-form-actions">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-check" aria-hidden="true"></i> Save changes</button>
                    <a class="btn btn-outline-secondary" href="/attendees">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
