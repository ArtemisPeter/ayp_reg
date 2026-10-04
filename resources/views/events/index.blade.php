@extends('layouts.app')

@section('title', 'Events')

@section('content')
    <div class="card mb-4">
        <h5 class="card-header">Add an Event</h5>
        <div class="card-body">
            <form method="POST" action="/events" class="row g-3" id="event-form">
                @csrf
                <div class="col-md-6">
                    <label class="form-label" for="name">Event name</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label d-block">Ranks and their points</label>
                    <div id="ranks">
                        <div class="d-flex gap-2 mb-2">
                            <input class="form-control" name="ranks[0][label]" placeholder="Champion" required>
                            <input class="form-control" name="ranks[0][points]" type="number" min="0" placeholder="Points" style="max-width: 8rem" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-rank">Add another rank</button>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Add Event</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Events</h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Event</th><th>Ranks and points</th><th></th></tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($events as $event)
                        <tr>
                            <td>{{ $event->name }}</td>
                            <td>
                                @foreach ($event->ranks as $rank)
                                    <span class="badge bg-label-primary me-1">{{ $rank->label }}: {{ $rank->points }}</span>
                                @endforeach
                            </td>
                            <td><a class="btn btn-sm btn-outline-primary" href="/events/{{ $event->id }}/score">Score</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var count = 1;
            document.getElementById('add-rank').addEventListener('click', function () {
                var row = document.createElement('div');
                row.className = 'd-flex gap-2 mb-2';
                row.innerHTML = '<input class="form-control" name="ranks[' + count + '][label]" placeholder="Rank" required>' +
                    '<input class="form-control" name="ranks[' + count + '][points]" type="number" min="0" placeholder="Points" style="max-width: 8rem" required>';
                document.getElementById('ranks').appendChild(row);
                count++;
            });
        })();
    </script>
@endsection
