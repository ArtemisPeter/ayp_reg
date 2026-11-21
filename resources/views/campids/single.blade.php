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
        <img class="art" src="{{ asset('images/camp-id/front.jpg') }}" alt="">
        <div class="first-name"><span>{{ $attendee->first_name }}</span></div>
        <div class="circuit" style="color: {{ $circuit->colour }}"><span>{{ $circuit->name }}</span></div>
    </div>
    <div class="card card-back">
        <img class="art" src="{{ asset('images/camp-id/back.jpg') }}" alt="">
        <div class="qr">{!! $qrSvg !!}</div>
    </div>
</div>
</body>
</html>
