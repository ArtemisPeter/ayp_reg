@extends('layouts.app')

@section('title', $event->name.': Scoring')

@section('content')
    @php($scoredCount = $circuits->filter(fn ($circuit) => isset($scores[$circuit->id]))->count())

    @include('partials.page-head', [
        'icon' => 'bx-edit',
        'title' => $event->name,
        'text' => 'Choose the Rank each Circuit earned. A choice is saved as soon as you make it.',
        'back' => ['href' => '/events', 'label' => 'All Events'],
    ])

    <div class="card">
        <div class="card-header duyog-panel-head">
            <h2><i class="bx bx-trophy" aria-hidden="true"></i> {{ $event->name }}: assign a Rank per Circuit</h2>
            <span class="duyog-panel-meta">{{ $scoredCount }} of {{ $circuits->count() }} scored</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th scope="col">Circuit</th><th scope="col">Rank</th><th scope="col" class="text-end">Points</th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($circuits as $circuit)
                        @php($currentRankId = $scores[$circuit->id] ?? null)
                        @php($currentRank = $event->ranks->firstWhere('id', $currentRankId))
                        <tr>
                            <td class="duyog-cell-strong">{{ $circuit->name }}</td>
                            <td>
                                <form method="POST" action="/events/{{ $event->id }}/score" class="duyog-inline-form">
                                    @csrf
                                    <input type="hidden" name="circuit_id" value="{{ $circuit->id }}">
                                    <select class="form-select form-select-sm" name="event_rank_id" onchange="this.form.submit()" aria-label="Rank for {{ $circuit->name }}">
                                        <option value="" disabled @selected(! $currentRank)>Not yet scored</option>
                                        @foreach ($event->ranks as $rank)
                                            <option value="{{ $rank->id }}" @selected($currentRankId === $rank->id)>{{ $rank->label }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><button class="btn btn-sm btn-outline-primary" type="submit">Save</button></noscript>
                                </form>
                            </td>
                            <td class="text-end duyog-cell-strong">{{ $currentRank->points ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
