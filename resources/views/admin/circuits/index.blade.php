@extends('layouts.app')

@section('title', 'Circuits')

@section('content')
    @include('partials.page-head', [
        'icon' => 'bx-buildings',
        'title' => 'Circuits & Churches',
        'text' => 'Each Circuit\'s colour is printed on its Camp IDs. Open a Circuit to manage its Local Churches.',
    ])

    <div class="card mb-4">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-plus-circle" aria-hidden="true"></i> Add a Circuit</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/circuits" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="off" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="colour">Colour on Camp IDs</label>
                    <input class="form-control form-control-color w-100" id="colour" name="colour" type="color" value="{{ old('colour', '#696cff') }}">
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-primary w-100" type="submit"><i class="bx bx-plus" aria-hidden="true"></i> Add Circuit</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-buildings" aria-hidden="true"></i> Circuits</h2>
            <span class="duyog-panel-meta">{{ $circuits->count() }} {{ Str::plural('circuit', $circuits->count()) }}</span>
        </div>
        @if ($circuits->isEmpty())
            <div class="duyog-empty">
                <i class="bx bx-buildings" aria-hidden="true"></i>
                <strong>No Circuits yet</strong>
                <span>Add the first Circuit with the form above.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Circuit</th><th scope="col">Colour</th><th scope="col">Local Churches</th><th scope="col"><span class="visually-hidden">Remove</span></th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($circuits as $circuit)
                            <tr>
                                <td>
                                    <form method="POST" action="/admin/circuits/{{ $circuit->id }}" class="duyog-inline-form">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control form-control-sm" name="name" value="{{ $circuit->name }}" aria-label="Circuit name" required>
                                        <input class="form-control form-control-sm form-control-color" name="colour" type="color" value="{{ $circuit->colour }}" title="{{ $circuit->colour }}" aria-label="Colour for {{ $circuit->name }}">
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                    </form>
                                </td>
                                <td><span class="duyog-swatch" style="--swatch: {{ $circuit->colour }}">{{ $circuit->colour }}</span></td>
                                <td class="text-nowrap"><a class="btn btn-sm btn-outline-secondary" href="/admin/circuits/{{ $circuit->id }}/churches"><i class="bx bx-church" aria-hidden="true"></i> {{ $circuit->local_churches_count }} churches</a></td>
                                <td class="text-end">
                                    <form method="POST" action="/admin/circuits/{{ $circuit->id }}" onsubmit="return confirm('Remove this Circuit?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Remove {{ $circuit->name }}"><i class="bx bx-trash" aria-hidden="true"></i> Remove</button>
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
