@extends('layouts.app')

@section('title', $circuit->name.': Local Churches')

@section('content')
    <p><a href="/admin/circuits"><i class="bx bx-chevron-left"></i> All Circuits</a></p>

    <div class="card mb-4">
        <h5 class="card-header">Add a Local Church to {{ $circuit->name }}</h5>
        <div class="card-body">
            <form method="POST" action="/admin/circuits/{{ $circuit->id }}/churches" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-9">
                    <label class="form-label" for="name">Church name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Add church</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Local Churches</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Church</th><th></th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($churches as $church)
                        <tr>
                            <td>
                                <form method="POST" action="/admin/circuits/{{ $circuit->id }}/churches/{{ $church->id }}" class="d-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input class="form-control form-control-sm" name="name" value="{{ $church->name }}" required>
                                    <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="/admin/circuits/{{ $circuit->id }}/churches/{{ $church->id }}" onsubmit="return confirm('Remove this church?')">
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
