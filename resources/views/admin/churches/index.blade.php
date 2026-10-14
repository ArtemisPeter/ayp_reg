@extends('layouts.app')

@section('title', $circuit->name.': Local Churches')

@section('content')
    @include('partials.page-head', [
        'icon' => 'bx-church',
        'title' => $circuit->name.': Local Churches',
        'text' => $canEdit
            ? 'Church names here are the official names rosters must use.'
            : 'Church names here are the official names your roster must use. To rename or remove a church, ask the admin.',
        'back' => $canEdit ? ['href' => '/admin/circuits', 'label' => 'All Circuits'] : null,
    ])

    <div class="card mb-4">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-plus-circle" aria-hidden="true"></i> Add a Local Church to {{ $circuit->name }}</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ $churchesUrl }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-9">
                    <label class="form-label" for="name">Church name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="off" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit"><i class="bx bx-plus" aria-hidden="true"></i> Add church</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-church" aria-hidden="true"></i> Local Churches</h2>
            <span class="duyog-panel-meta">{{ $churches->count() }} {{ Str::plural('church', $churches->count()) }}</span>
        </div>
        @if ($churches->isEmpty())
            <div class="duyog-empty">
                <i class="bx bx-church" aria-hidden="true"></i>
                <strong>No churches in {{ $circuit->name }} yet</strong>
                <span>Add the first Local Church with the form above.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Church</th>@if ($canEdit)<th scope="col"><span class="visually-hidden">Remove</span></th>@endif</tr></thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($churches as $church)
                            @unless ($canEdit)
                                <tr><td>{{ $church->name }}</td></tr>
                                @continue
                            @endunless
                            <tr>
                                <td>
                                    <form method="POST" action="/admin/circuits/{{ $circuit->id }}/churches/{{ $church->id }}" class="duyog-inline-form">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control form-control-sm" name="name" value="{{ $church->name }}" aria-label="Church name" required>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="/admin/circuits/{{ $circuit->id }}/churches/{{ $church->id }}" onsubmit="return confirm('Remove this church?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Remove {{ $church->name }}"><i class="bx bx-trash" aria-hidden="true"></i> Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
