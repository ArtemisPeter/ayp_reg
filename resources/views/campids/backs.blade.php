<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Camp ID backs: {{ $circuit->name }}{{ $church ? ' · '.$church->name : '' }}</title>
    @include('campids.styles')
</head>
<body>
<div class="toolbar">
    <span>{{ $circuit->name }}{{ $church ? ' · '.$church->name : '' }}: backs (QR side). Print double-sided, flipping on the long edge.</span>
</div>
@foreach ($sheets as $sheet)
    <div class="sheet">
        @foreach ($sheet as $svg)
            <div class="card card-back">
                @if ($svg !== null)
                    <div class="qr">{!! $svg !!}</div>
                @endif
            </div>
        @endforeach
    </div>
@endforeach
</body>
</html>
