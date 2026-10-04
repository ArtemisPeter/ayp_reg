<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Camp ID: {{ $attendee->first_name }}</title>
    @include('campids.styles')
</head>
<body>
<div class="toolbar">
    <span>Camp ID for {{ $attendee->first_name }}: front on the left, back on the right. Cut along the dashed lines.</span>
    <a href="/attendees">Back to Attendees</a>
    <form method="POST" action="/camp-ids/{{ $circuit->id }}/printed" onsubmit="return confirm('Only confirm if the card printed correctly. Mark it as printed?')">
        @csrf
        <input type="hidden" name="attendee_ids[]" value="{{ $attendee->id }}">
        <button type="submit">Mark as printed</button>
    </form>
</div>
<div class="sheet">
    <div class="card card-front">
        <div class="band" style="background: {{ $circuit->colour }}"></div>
        <div class="first-name">{{ $attendee->first_name }}</div>
        <div class="circuit" style="color: {{ $circuit->colour }}">{{ $circuit->name }}</div>
    </div>
    <div class="card card-back">
        <div class="qr">{!! $qrSvg !!}</div>
    </div>
</div>
</body>
</html>
