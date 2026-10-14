@extends('layouts.app')

@section('title', 'Gate Scan Review')

@section('content')
    @include('partials.page-head', [
        'icon' => 'bx-flag',
        'title' => 'Gate Scan Review',
        'text' => 'Mismatched scans and scans logged by name only (not matched to an Attendee).',
    ])

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-error" aria-hidden="true"></i> Scans needing review</h2>
            <span class="duyog-panel-meta">{{ $scans->count() }} {{ Str::plural('scan', $scans->count()) }}</span>
        </div>
        @if ($scans->isEmpty())
            <div class="duyog-empty">
                <i class="bx bx-check-shield" aria-hidden="true"></i>
                <strong>Nothing needs review</strong>
                <span>Every gate scan so far matched an Attendee.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th scope="col">Time</th><th scope="col">Direction</th><th scope="col">Attendee</th><th scope="col">Scanned by</th><th scope="col">Note</th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($scans as $scan)
                            <tr>
                                <td class="text-nowrap">{{ $scan->scanned_at->format('Y-m-d g:i A') }}</td>
                                <td><span class="badge {{ $scan->direction->value === 'in' ? 'bg-label-success' : 'bg-label-danger' }}">{{ strtoupper($scan->direction->value) }}</span></td>
                                <td class="duyog-cell-strong">{{ $scan->attendee?->first_name ?? $scan->manual_name.' (by name)' }}</td>
                                <td>{{ $scan->scannedBy->name }}</td>
                                <td>{{ $scan->message }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
