<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Camp ID fronts: {{ $circuit->name }}{{ $church ? ' · '.$church->name : '' }}</title>
    @include('campids.styles')
</head>
<body>
<div class="toolbar">
    <span>{{ $circuit->name }}{{ $church ? ' · '.$church->name : '' }}: {{ $attendees->count() }} Camp ID(s) to print, fronts</span>
    <a href="/camp-ids">Back to Camp IDs</a>
    @if ($attendees->isNotEmpty())
        <form method="POST" action="/camp-ids/{{ $circuit->id }}/printed" onsubmit="return confirm('Only confirm if both the fronts and the backs printed correctly. Mark these {{ $attendees->count() }} Camp ID(s) as printed?')">
            @csrf
            @foreach ($attendees as $attendee)
                <input type="hidden" name="attendee_ids[]" value="{{ $attendee->id }}">
            @endforeach
            <button type="submit">Mark these as printed</button>
        </form>
    @endif
</div>
@foreach ($attendees->chunk(4) as $sheet)
    <div class="sheet">
        @foreach ($sheet as $attendee)
            <div class="card card-front">
                <div class="band" style="background: {{ $circuit->colour }}"></div>
                <div class="first-name">{{ $attendee->first_name }}</div>
                <div class="circuit" style="color: {{ $circuit->colour }}">{{ $circuit->name }}</div>
            </div>
        @endforeach
    </div>
@endforeach
</body>
</html>
