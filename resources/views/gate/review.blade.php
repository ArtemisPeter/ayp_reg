@extends('layouts.app')

@section('title', 'Gate Scan Review')

@section('content')
    <div class="card">
        <h5 class="card-header">Scans needing review</h5>
        <p class="text-muted px-3">Mismatched scans and scans logged by name only (not matched to an Attendee).</p>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Time</th><th>Direction</th><th>Attendee</th><th>Scanned by</th><th>Note</th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($scans as $scan)
                        <tr>
                            <td>{{ $scan->scanned_at->format('Y-m-d g:i A') }}</td>
                            <td>{{ strtoupper($scan->direction->value) }}</td>
                            <td>{{ $scan->attendee?->first_name ?? $scan->manual_name.' (by name)' }}</td>
                            <td>{{ $scan->scannedBy->name }}</td>
                            <td>{{ $scan->message }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
