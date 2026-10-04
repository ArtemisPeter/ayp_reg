@extends('layouts.app')

@section('title', 'Camp IDs')

@section('content')
    <div class="card">
        <h5 class="card-header">Camp IDs waiting to be printed</h5>
        <div class="card-body">
            <p class="text-muted">Print the fronts, then the backs (double-sided, flipping on the long edge). Only when both sides came out right, use "Mark as printed" at the top of the fronts page. Viewing a page never marks anything as printed.</p>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Circuit</th><th>Colour</th><th>Waiting</th><th></th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($circuits as $circuit)
                        <tr>
                            <td>{{ $circuit->name }}</td>
                            <td><span class="badge" style="background: {{ $circuit->colour }}">{{ $circuit->colour }}</span></td>
                            <td>{{ $circuit->pending_count }}</td>
                            <td class="text-nowrap">
                                @if ($circuit->pending_count > 0)
                                    <a class="btn btn-sm btn-outline-primary" target="_blank" href="/camp-ids/{{ $circuit->id }}/fronts">Fronts</a>
                                    <a class="btn btn-sm btn-outline-primary" target="_blank" href="/camp-ids/{{ $circuit->id }}/backs">Backs</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
