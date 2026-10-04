@extends('layouts.app')

@section('title', $event->name.': Scoring')

@section('content')
    <p><a href="/events"><i class="bx bx-chevron-left"></i> All Events</a></p>

    <div class="card">
        <h5 class="card-header">{{ $event->name }}: assign a Rank per Circuit</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Circuit</th><th>Rank</th><th>Points</th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($circuits as $circuit)
                        @php($currentRankId = $scores[$circuit->id] ?? null)
                        @php($currentRank = $event->ranks->firstWhere('id', $currentRankId))
                        <tr>
                            <td>{{ $circuit->name }}</td>
                            <td>
                                <form method="POST" action="/events/{{ $event->id }}/score" class="d-flex gap-2">
                                    @csrf
                                    <input type="hidden" name="circuit_id" value="{{ $circuit->id }}">
                                    <select class="form-select form-select-sm" name="event_rank_id" onchange="this.form.submit()">
                                        <option value="" disabled @selected(! $currentRank)>Not yet scored</option>
                                        @foreach ($event->ranks as $rank)
                                            <option value="{{ $rank->id }}" @selected($currentRankId === $rank->id)>{{ $rank->label }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><button class="btn btn-sm btn-outline-primary" type="submit">Save</button></noscript>
                                </form>
                            </td>
                            <td>{{ $currentRank->points ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
