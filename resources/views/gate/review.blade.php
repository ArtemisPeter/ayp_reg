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
                    <thead><tr><th scope="col">Time</th><th scope="col">Direction</th><th scope="col">Attendee</th><th scope="col">Circuit</th><th scope="col">Scanned by</th><th scope="col">Note</th><th scope="col"><span class="visually-hidden">Delete</span></th></tr></thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($scans as $scan)
                            @php($attendee = $scan->attendee)
                            @php($scanLabel = $attendee ? trim("{$attendee->first_name} {$attendee->last_name}") : $scan->manual_name)
                            <tr>
                                <td class="text-nowrap">{{ $scan->scanned_at->format('Y-m-d g:i A') }}</td>
                                <td><span class="badge {{ $scan->direction->value === 'in' ? 'bg-label-success' : 'bg-label-danger' }}">{{ strtoupper($scan->direction->value) }}</span></td>
                                <td>
                                    @if ($attendee)
                                        <span class="duyog-cell-strong">{{ $attendee->last_name }}, {{ $attendee->first_name }}{{ $attendee->middle_name ? ' '.$attendee->middle_name : '' }}</span>
                                        @if ($attendee->trashed())
                                            <span class="badge bg-label-secondary mt-1">Removed attendee</span>
                                        @endif
                                    @else
                                        <span class="duyog-cell-strong">{{ $scan->manual_name }}</span>
                                        <span class="duyog-cell-sub">Entered by name</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($attendee?->localChurch)
                                        {{ $attendee->localChurch->circuit?->name }}<span class="duyog-cell-sub">{{ $attendee->localChurch->name }}</span>
                                    @else
                                        <span class="text-muted">&#8211;</span>
                                    @endif
                                </td>
                                <td>{{ $scan->scannedBy->name }}</td>
                                <td>{{ $scan->message }}</td>
                                <td class="text-end">
                                    <form method="POST" action="/gate/scans/{{ $scan->id }}" onsubmit="return confirm('Delete this scan? It will no longer count in this person\'s IN/OUT record.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Delete the {{ strtoupper($scan->direction->value) }} scan for {{ $scanLabel }} at {{ $scan->scanned_at->format('g:i A') }}"><i class="bx bx-trash" aria-hidden="true"></i> Delete</button>
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
