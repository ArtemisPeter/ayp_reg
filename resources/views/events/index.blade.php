@extends('layouts.app')

@section('title', 'Events')

@section('content')
    @include('partials.page-head', [
        'icon' => 'bx-trophy',
        'title' => 'Events',
        'text' => 'Set up each contest with its ranks and points, then score every Circuit.',
    ])

    <div class="duyog-panel-grid">
        <div class="card">
            <div class="card-header duyog-panel-head">
                <h2><i class="bx bx-list-ol" aria-hidden="true"></i> Events</h2>
                <span class="duyog-panel-meta">{{ $events->count() }} {{ Str::plural('event', $events->count()) }}</span>
            </div>
            @if ($events->isEmpty())
                <div class="duyog-empty">
                    <i class="bx bx-trophy" aria-hidden="true"></i>
                    <strong>No events yet</strong>
                    <span>Add the first event with the form beside this list.</span>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th scope="col">Event</th><th scope="col">Ranks and points</th><th scope="col"><span class="visually-hidden">Score</span></th></tr></thead>
                        <tbody class="table-border-bottom-0">
                            @foreach ($events as $event)
                                <tr>
                                    <td class="duyog-cell-strong">{{ $event->name }}</td>
                                    <td>
                                        <span class="duyog-chip-list">
                                            @foreach ($event->ranks as $rank)
                                                <span class="duyog-chip">{{ $rank->label }} <b>{{ $rank->points }}</b></span>
                                            @endforeach
                                        </span>
                                    </td>
                                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/events/{{ $event->id }}/score"><i class="bx bx-edit" aria-hidden="true"></i> Score</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header duyog-panel-head">
                <h2><i class="bx bx-plus-circle" aria-hidden="true"></i> Add an Event</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="/events" class="row g-3" id="event-form">
                    @csrf
                    <div class="col-12">
                        <label class="form-label" for="name">Event name</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label d-block" id="ranks-label">Ranks and their points</label>
                        <div id="ranks" role="group" aria-labelledby="ranks-label">
                            <div class="duyog-rankfield-row">
                                <input class="form-control" name="ranks[0][label]" placeholder="Champion" aria-label="Rank 1 name" required>
                                <input class="form-control duyog-rank-points" name="ranks[0][points]" type="number" min="0" placeholder="Points" aria-label="Rank 1 points" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="add-rank"><i class="bx bx-plus" aria-hidden="true"></i> Add another rank</button>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit"><i class="bx bx-check" aria-hidden="true"></i> Add Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var count = 1;
            document.getElementById('add-rank').addEventListener('click', function () {
                var row = document.createElement('div');
                row.className = 'duyog-rankfield-row';
                row.innerHTML = '<input class="form-control" name="ranks[' + count + '][label]" placeholder="Rank" aria-label="Rank ' + (count + 1) + ' name" required>' +
                    '<input class="form-control duyog-rank-points" name="ranks[' + count + '][points]" type="number" min="0" placeholder="Points" aria-label="Rank ' + (count + 1) + ' points" required>';
                document.getElementById('ranks').appendChild(row);
                row.querySelector('input').focus();
                count++;
            });
        })();
    </script>
@endsection
