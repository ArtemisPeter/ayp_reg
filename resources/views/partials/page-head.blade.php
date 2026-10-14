@isset($back)
    <a class="duyog-back-link" href="{{ $back['href'] }}"><i class="bx bx-left-arrow-alt" aria-hidden="true"></i> {{ $back['label'] }}</a>
@endisset
<header class="duyog-page-head">
    <span class="duyog-page-head-icon"><i class="bx {{ $icon }}" aria-hidden="true"></i></span>
    <div>
        <h1>{{ $title }}</h1>
        @isset($text)
            <p>{{ $text }}</p>
        @endisset
    </div>
</header>
