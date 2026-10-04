@extends('layouts.portal')

@section('title', $camp ? $camp->theme." | DUYOG '26" : "DUYOG '26")

@section('content')
    <section class="duyog-hero duyog-hero-text-only">
        <div class="duyog-hero-copy">
            <p class="duyog-eyebrow mb-3">South Mindanao District Youth Camp &#183; {{ $camp?->year ?? '2026' }}</p>
            <div class="duyog-wordmark mb-3" style="font-size: clamp(1.45rem, 4vw, 2.25rem);">DUYOG '26</div>
            @if ($camp)
                <h1 class="duyog-heading duyog-hero-title">{{ $camp->theme }}</h1>
                @if ($camp->tagline)
                    <p class="duyog-hero-tagline">{{ $camp->tagline }}</p>
                @endif
                @if ($camp->verse_reference)
                    <p class="duyog-verse mt-3 mb-0">{{ $camp->verse_reference }}</p>
                @endif
                <div class="duyog-hero-facts">
                    <div class="duyog-fact"><span><i class="bx bx-calendar me-1"></i> Gathering</span><strong>{{ $camp->starts_on->format('M d') }}&#8211;{{ $camp->ends_on->format('d, Y') }}</strong></div>
                    @if ($camp->venue)
                        <div class="duyog-fact"><span><i class="bx bx-map me-1"></i> Where</span><strong>{{ $camp->venue }}</strong></div>
                    @endif
                </div>
                @if ($camp->pre_registration_fee || $camp->on_site_fee)
                    <div class="duyog-hero-facts">
                        @if ($camp->pre_registration_fee)
                            <div class="duyog-fact"><span>Pre-registration</span><strong>&#8369;{{ number_format($camp->pre_registration_fee, 0) }}</strong></div>
                        @endif
                        @if ($camp->on_site_fee)
                            <div class="duyog-fact"><span>On-site registration</span><strong>&#8369;{{ number_format($camp->on_site_fee, 0) }}</strong></div>
                        @endif
                    </div>
                @endif
            @else
                <h1 class="duyog-heading duyog-hero-title">ROOTED</h1>
                <p class="duyog-hero-tagline">Growing deeper. Reaching higher. Living for Christ.</p>
                <p class="duyog-verse mt-3">Colossians 2:6&#8211;7</p>
                <div class="duyog-fact mt-4"><span>Registration</span><strong>Opens soon</strong></div>
            @endif
        </div>
    </section>

    @php
        $rankedCircuits = $contestLeaderboard->filter(fn ($row) => $row['rank'] !== null);
        $podium = $rankedCircuits->take(3);
        $podiumCircuits = $podium->pluck('circuit');
        $remainingCircuits = $contestLeaderboard->reject(fn ($row) => $podiumCircuits->contains($row['circuit']));
        $topPoints = (int) ($rankedCircuits->first()['points'] ?? 0);
        $leaderboardCount = $contestLeaderboard->count();
    @endphp

    <section class="duyog-contest-board mb-5" aria-labelledby="contest-leaderboard-title">
        <header class="duyog-contest-header">
            <div class="duyog-ladder-icon"><i class="bx bx-trophy"></i></div>
            <div>
                <p class="duyog-eyebrow mb-1">Circuit Contest</p>
                <h2 id="contest-leaderboard-title">Leaderboard</h2>
            </div>
            <span class="duyog-contest-season">DUYOG '26</span>
        </header>
        <div class="duyog-leaderboard-tools">
            <label class="duyog-leaderboard-search">
                <i class="bx bx-search" aria-hidden="true"></i>
                <input type="search" data-leaderboard-search placeholder="Find a circuit" aria-label="Find a circuit in the leaderboard" autocomplete="off">
                <kbd>/</kbd>
            </label>
            <span class="duyog-leaderboard-count" data-leaderboard-count>{{ $leaderboardCount }} {{ $leaderboardCount === 1 ? 'circuit' : 'circuits' }}</span>
        </div>

        @if ($podium->isNotEmpty())
            <div class="duyog-podium" aria-label="Top circuits">
                @foreach ($podium as $row)
                    @php($scorePercent = $topPoints > 0 ? min(100, (int) round($row['points'] / $topPoints * 100)) : 0)
                    <article class="duyog-podium-card {{ $row['rank'] === 1 ? 'is-leader' : '' }}" data-leaderboard-entry>
                        <span class="duyog-podium-medal"><i class="bx {{ $row['rank'] === 1 ? 'bx-crown' : ($row['rank'] === 2 ? 'bx-medal' : 'bx-award') }}"></i></span>
                        <span class="duyog-podium-rank">#{{ $row['rank'] }}</span>
                        <strong class="duyog-podium-name">{{ $row['circuit'] }}</strong>
                        <span class="duyog-podium-score">{{ number_format($row['points']) }}</span>
                        <span class="duyog-podium-label">points</span>
                        <span class="duyog-score-track" role="meter" aria-label="{{ $row['circuit'] }} points relative to the leader" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $scorePercent }}"><span style="width: {{ $scorePercent }}%"></span></span>
                        @if ($row['tie']) <span class="duyog-podium-tie">Tied</span> @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if ($remainingCircuits->isNotEmpty())
            <div class="duyog-rank-list">
                <div class="duyog-rank-list-heading"><span>Rank</span><span>Circuit</span><span class="text-end">Points</span></div>
                @foreach ($remainingCircuits as $row)
                    @php($scorePercent = $topPoints > 0 ? min(100, (int) round($row['points'] / $topPoints * 100)) : 0)
                    <div class="duyog-rank-row" data-leaderboard-entry>
                        <span class="duyog-rank-number">{{ $row['rank'] ?? '-' }}</span>
                        <span class="duyog-rank-circuit">{{ $row['circuit'] }} @if ($row['tie']) <span class="duyog-podium-tie">Tied</span> @endif
                            <span class="duyog-rank-progress" aria-hidden="true"><span style="width: {{ $scorePercent }}%"></span></span>
                        </span>
                        <strong class="duyog-rank-score">{{ number_format($row['points']) }}</strong>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="duyog-no-results" data-leaderboard-empty hidden>No circuits match your search.</p>
    </section>

    <section class="duyog-card duyog-resource-card mb-5 p-4 p-lg-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
        <div><p class="duyog-eyebrow mb-2">Church leaders</p><h2 class="h4 mb-2">Registration resources</h2><p class="mb-0">Get the roster template and check official Circuit and Church names before preparing your list.</p></div>
        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
            <a class="btn duyog-btn-primary" href="/template.csv"><i class="bx bx-download me-1"></i> CSV template</a>
            <a class="btn btn-outline-light" href="/churches.csv"><i class="bx bx-list-ul me-1"></i> Circuit &amp; Church list</a>
        </div>
    </section>

    <script>
        (() => {
            const search = document.querySelector('[data-leaderboard-search]');
            const entries = [...document.querySelectorAll('[data-leaderboard-entry]')];
            const count = document.querySelector('[data-leaderboard-count]');
            const empty = document.querySelector('[data-leaderboard-empty]');

            if (!search) return;

            search.addEventListener('input', () => {
                const query = search.value.trim().toLocaleLowerCase();
                let visible = 0;

                entries.forEach((entry) => {
                    const matches = entry.textContent.toLocaleLowerCase().includes(query);
                    entry.hidden = !matches;
                    visible += matches ? 1 : 0;
                });

                count.textContent = `${visible} ${visible === 1 ? 'circuit' : 'circuits'}`;
                empty.hidden = visible > 0;
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    event.preventDefault();
                    search.focus();
                }

                if (event.key === 'Escape' && document.activeElement === search) {
                    search.value = '';
                    search.dispatchEvent(new Event('input'));
                    search.blur();
                }
            });
        })();
    </script>
@endsection