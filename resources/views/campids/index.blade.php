@extends('layouts.app')

@section('title', 'Camp IDs')

@section('content')
    @php($waitingTotal = $circuits->sum('pending_count'))

    @include('partials.page-head', [
        'icon' => 'bx-id-card',
        'title' => 'Camp IDs',
        'text' => 'Print Camp IDs one Circuit at a time. Viewing a page never marks anything as printed.',
    ])

    <ol class="duyog-steps-inline mb-4">
        <li>Print the fronts for a Circuit.</li>
        <li>Print the backs, double-sided, flipping on the long edge.</li>
        <li>Only when both sides came out right, use "Mark as printed" at the top of the fronts page.</li>
    </ol>

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-printer" aria-hidden="true"></i> Camp IDs waiting to be printed</h2>
            <span class="duyog-panel-meta">{{ number_format($waitingTotal) }} waiting</span>
        </div>
        @if ($circuits->isEmpty())
            <div class="duyog-empty">
                <i class="bx bx-buildings" aria-hidden="true"></i>
                <strong>No Circuits yet</strong>
                <span>Circuits appear here once an administrator adds them.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Circuit</th><th scope="col">Colour</th><th scope="col">Waiting</th><th scope="col"><span class="visually-hidden">Print</span></th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($circuits as $circuit)
                            <tr>
                                <td class="duyog-cell-strong">{{ $circuit->name }}</td>
                                <td><span class="duyog-swatch" style="--swatch: {{ $circuit->colour }}">{{ $circuit->colour }}</span></td>
                                <td>
                                    @if ($circuit->pending_count > 0)
                                        <span class="badge bg-label-warning">{{ $circuit->pending_count }}</span>
                                    @else
                                        <span class="badge bg-label-success"><i class="bx bx-check" aria-hidden="true"></i> {{ $circuit->pending_count }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap text-end">
                                    @if ($circuit->pending_count > 0)
                                        <span class="duyog-row-actions">
                                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="/camp-ids/{{ $circuit->id }}/fronts"><i class="bx bx-printer" aria-hidden="true"></i> Fronts</a>
                                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="/camp-ids/{{ $circuit->id }}/backs"><i class="bx bx-printer" aria-hidden="true"></i> Backs</a>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
