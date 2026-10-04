@extends('layouts.app')

@section('title', 'Circuits')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">Add a Circuit</h5>
        <div class="card-body">
            <form method="POST" action="/admin/circuits" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-6">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="colour">Colour on Camp IDs</label>
                    <input class="form-control form-control-color w-100" id="colour" name="colour" type="color" value="{{ old('colour', '#696cff') }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Add Circuit</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Circuits</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Circuit</th><th>Colour</th><th>Local Churches</th><th></th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($circuits as $circuit)
                        <tr>
                            <td>
                                <form method="POST" action="/admin/circuits/{{ $circuit->id }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="form-control form-control-sm" name="name" value="{{ $circuit->name }}" required>
                                    <input class="form-control form-control-sm form-control-color" name="colour" type="color" value="{{ $circuit->colour }}" title="{{ $circuit->colour }}">
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                </form>
                            </td>
                            <td><span class="badge" style="background: {{ $circuit->colour }}">{{ $circuit->colour }}</span></td>
                            <td><a href="/admin/circuits/{{ $circuit->id }}/churches">{{ $circuit->local_churches_count }} churches</a></td>
                            <td>
                                <form method="POST" action="/admin/circuits/{{ $circuit->id }}" onsubmit="return confirm('Remove this Circuit?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
