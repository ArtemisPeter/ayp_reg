@extends('layouts.portal')

@section('title', $camp ? $camp->theme." | DUYOG '26" : "DUYOG '26")

@section('content')
    @php
        $today = today();
        $campState = match (true) {
            ! $camp => null,
            $today->lt($camp->starts_on) => 'upcoming',
            $today->lte($camp->ends_on) => 'live',
            default => 'ended',
        };
        $daysToGo = $campState === 'upcoming' ? (int) $today->diffInDays($camp->starts_on) : null;
    @endphp

    <section class="duyog-lp-hero" aria-labelledby="camp-theme">
        <div class="duyog-lp-hero-copy">
            <p class="duyog-eyebrow mb-3">South Mindanao District Youth Camp &#183; {{ $camp?->year ?? '2026' }}</p>
            <div class="duyog-wordmark duyog-lp-wordmark mb-3">DUYOG '26</div>
            @if ($camp)
                <h1 id="camp-theme" class="duyog-heading duyog-lp-title">{{ $camp->theme }}</h1>
                @if ($camp->tagline)
                    <p class="duyog-lp-tagline">{{ $camp->tagline }}</p>
                @endif
                @if ($camp->verse_reference)
                    <p class="duyog-lp-verse"><i class="bx bx-book-open" aria-hidden="true"></i> {{ $camp->verse_reference }}</p>
                @endif
            @else
                <h1 id="camp-theme" class="duyog-heading duyog-lp-title">ROOTED</h1>
                <p class="duyog-lp-tagline">Growing deeper. Reaching higher. Living for Christ.</p>
                <p class="duyog-lp-verse"><i class="bx bx-book-open" aria-hidden="true"></i> Colossians 2:6&#8211;7</p>
            @endif
            <div class="duyog-lp-actions">
                <a class="btn duyog-btn-primary duyog-lp-btn" href="#register"><i class="bx bx-edit-alt" aria-hidden="true"></i> How to register</a>
                <a class="btn duyog-lp-btn duyog-lp-btn-ghost" href="#leaderboard"><i class="bx bx-trophy" aria-hidden="true"></i> View leaderboard</a>
            </div>
        </div>

        <aside class="duyog-lp-pass" aria-label="Camp details">
            @if ($campState === 'upcoming')
                <div class="duyog-lp-countdown" data-countdown-to="{{ $camp->starts_on->format('Y-m-d') }}">
                    <p class="duyog-lp-countdown-days"><strong data-countdown-days>{{ $daysToGo }}</strong> <span data-countdown-days-label>{{ $daysToGo === 1 ? 'day to go' : 'days to go' }}</span></p>
                    <div class="duyog-lp-countdown-clock" data-countdown-clock aria-hidden="true" hidden>
                        <span><b data-countdown-hours>00</b> hrs</span>
                        <span><b data-countdown-minutes>00</b> min</span>
                        <span><b data-countdown-seconds>00</b> sec</span>
                    </div>
                </div>
            @elseif ($campState === 'live')
                <div class="duyog-lp-status is-live"><span class="duyog-lp-status-dot" aria-hidden="true"></span> Happening now</div>
            @elseif ($campState === 'ended')
                <div class="duyog-lp-status">This camp has ended</div>
            @else
                <div class="duyog-lp-status"><span>Registration</span> <strong>Opens soon</strong></div>
            @endif

            @if ($camp)
                <dl class="duyog-lp-details">
                    <div>
                        <dt><i class="bx bx-calendar" aria-hidden="true"></i> Gathering</dt>
                        <dd>{{ $camp->starts_on->format('M d') }}&#8211;{{ $camp->ends_on->format('d, Y') }}</dd>
                    </div>
                    @if ($camp->venue)
                        <div>
                            <dt><i class="bx bx-map" aria-hidden="true"></i> Where</dt>
                            <dd>{{ $camp->venue }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($camp->pre_registration_fee || $camp->on_site_fee)
                    <dl class="duyog-lp-fees">
                        @if ($camp->pre_registration_fee)
                            <div>
                                <dt>Pre-registration</dt>
                                <dd>&#8369;{{ number_format($camp->pre_registration_fee, 0) }}</dd>
                            </div>
                        @endif
                        @if ($camp->on_site_fee)
                            <div>
                                <dt>On-site registration</dt>
                                <dd>&#8369;{{ number_format($camp->on_site_fee, 0) }}</dd>
                            </div>
                        @endif
                    </dl>
                @endif
            @endif
        </aside>
    </section>

    @if ((int) $camp?->year === 2026)
        <section id="camp" class="duyog-lp-about duyog-lp-section" aria-labelledby="camp-title" data-reveal>
            <a class="duyog-lp-poster" href="{{ asset('downloads/duyog-26-rooted-camp-guide.pdf') }}" target="_blank" rel="noopener" aria-label="Open the DUYOG '26 camp guide (PDF)">
                <img src="{{ asset('images/duyog-speaker-poster.webp') }}" alt="Official DUYOG '26 poster: {{ $camp->theme }}, with speaker Rev. Jade T. Diaz" width="900" height="1273" loading="lazy" decoding="async">
            </a>
            <div class="duyog-lp-about-copy">
                <p class="duyog-eyebrow mb-2">The camp</p>
                <h2 id="camp-title">Camp at a glance</h2>
                <p>DUYOG is the Youth Camp of the South Mindanao District, Alliance Youth of the Philippines.@if ($camp->verse_reference) This year's theme, {{ $camp->theme }}, is drawn from {{ $camp->verse_reference }}.@endif</p>
                <dl class="duyog-lp-facts">
                    <div>
                        <dt><i class="bx bx-microphone" aria-hidden="true"></i> Speaker</dt>
                        <dd>Rev. Jade T. Diaz</dd>
                    </div>
                </dl>
                <a class="btn duyog-lp-btn duyog-lp-btn-ghost" href="{{ asset('downloads/duyog-26-rooted-camp-guide.pdf') }}" download><i class="bx bx-download" aria-hidden="true"></i> Download the camp guide <span class="duyog-lp-btn-meta">PDF, 3 MB</span></a>
            </div>
        </section>
    @endif

    @php
        $rankedCircuits = $contestLeaderboard->filter(fn ($row) => $row['rank'] !== null);
        $podium = $rankedCircuits->take(3);
        $podiumCircuits = $podium->pluck('circuit');
        $remainingCircuits = $contestLeaderboard->reject(fn ($row) => $podiumCircuits->contains($row['circuit']));
        $topPoints = (int) ($rankedCircuits->first()['points'] ?? 0);
        $leaderboardCount = $contestLeaderboard->count();
    @endphp

    <section id="leaderboard" class="duyog-contest-board duyog-lp-section" aria-labelledby="contest-leaderboard-title" data-reveal>
        <header class="duyog-contest-header">
            <div class="duyog-ladder-icon"><i class="bx bx-trophy" aria-hidden="true"></i></div>
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
                <kbd aria-hidden="true">/</kbd>
            </label>
            <span class="duyog-leaderboard-count" data-leaderboard-count aria-live="polite">{{ $leaderboardCount }} {{ $leaderboardCount === 1 ? 'circuit' : 'circuits' }}</span>
        </div>

        @if ($podium->isNotEmpty())
            <div class="duyog-podium {{ $podium->count() === 3 ? 'has-three' : '' }}" aria-label="Top circuits">
                @foreach ($podium as $row)
                    @php($scorePercent = $topPoints > 0 ? min(100, (int) round($row['points'] / $topPoints * 100)) : 0)
                    <article class="duyog-podium-card {{ $row['rank'] === 1 ? 'is-leader' : '' }}" data-leaderboard-entry>
                        <span class="duyog-podium-medal"><i class="bx {{ $row['rank'] === 1 ? 'bx-crown' : ($row['rank'] === 2 ? 'bx-medal' : 'bx-award') }}" aria-hidden="true"></i></span>
                        <span class="duyog-podium-rank">#{{ $row['rank'] }}</span>
                        <strong class="duyog-podium-name">{{ $row['circuit'] }}</strong>
                        <span class="duyog-podium-score">{{ number_format($row['points']) }}</span>
                        <span class="duyog-podium-label">points</span>
                        <span class="duyog-score-track" role="meter" aria-label="{{ $row['circuit'] }} points relative to the leader" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $scorePercent }}"><span style="width: {{ $scorePercent }}%"></span></span>
                        @if ($row['tie']) <span class="duyog-podium-tie">Tied</span> @endif
                    </article>
                @endforeach
            </div>
        @elseif ($leaderboardCount > 0)
            <p class="duyog-lp-board-note"><i class="bx bx-time-five" aria-hidden="true"></i> No contest points yet. Standings appear here as soon as the first event is scored.</p>
        @endif

        @if ($remainingCircuits->isNotEmpty())
            <div class="duyog-rank-list">
                <div class="duyog-rank-list-heading"><span>Rank</span><span>Circuit</span><span class="text-end">Points</span></div>
                @foreach ($remainingCircuits as $row)
                    @php($scorePercent = $topPoints > 0 ? min(100, (int) round($row['points'] / $topPoints * 100)) : 0)
                    <div class="duyog-rank-row" data-leaderboard-entry>
                        <span class="duyog-rank-number">{{ $row['rank'] ?? '–' }}</span>
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

    <section id="register" class="duyog-lp-register duyog-lp-section" aria-labelledby="register-title" data-reveal>
        <header class="duyog-lp-register-header">
            <p class="duyog-eyebrow mb-2">Church leaders</p>
            <h2 id="register-title">Register your youth in three steps</h2>
            <p>Rosters are prepared per church and uploaded by your circuit's staff.</p>
        </header>
        <ol class="duyog-lp-steps">
            <li class="duyog-lp-step">
                <span class="duyog-lp-step-number" aria-hidden="true">1</span>
                <h3>Get the roster template</h3>
                <p>Start from the official CSV so every column is already in the right place.</p>
                <a class="btn duyog-btn-primary duyog-lp-btn" href="/template.csv"><i class="bx bx-download" aria-hidden="true"></i> CSV template</a>
            </li>
            <li class="duyog-lp-step">
                <span class="duyog-lp-step-number" aria-hidden="true">2</span>
                <h3>Check the official names</h3>
                <p>Match your Circuit and Church names against the official list before filling in your roster.</p>
                <a class="btn duyog-lp-btn duyog-lp-btn-ghost" href="/churches.csv"><i class="bx bx-list-ul" aria-hidden="true"></i> Circuit &amp; Church list</a>
            </li>
            <li class="duyog-lp-step">
                <span class="duyog-lp-step-number" aria-hidden="true">3</span>
                <h3>Hand in your roster</h3>
                <p>Circuit presidents and secretaries sign in to upload the completed roster.</p>
                <a class="btn duyog-lp-btn duyog-lp-btn-ghost" href="{{ route('login') }}"><i class="bx bx-lock-alt" aria-hidden="true"></i> Staff sign in</a>
            </li>
        </ol>
    </section>

    @if ($campState === 'upcoming')
        <script>
            (() => {
                const countdown = document.querySelector('[data-countdown-to]');
                const [year, month, day] = countdown.dataset.countdownTo.split('-').map(Number);
                const target = new Date(year, month - 1, day).getTime();
                const clock = countdown.querySelector('[data-countdown-clock]');
                const pad = (value) => String(value).padStart(2, '0');

                const tick = () => {
                    const remaining = Math.max(0, target - Date.now());
                    const days = Math.floor(remaining / 86400000);

                    countdown.querySelector('[data-countdown-days]').textContent = days;
                    countdown.querySelector('[data-countdown-days-label]').textContent = days === 1 ? 'day to go' : 'days to go';
                    countdown.querySelector('[data-countdown-hours]').textContent = pad(Math.floor(remaining / 3600000) % 24);
                    countdown.querySelector('[data-countdown-minutes]').textContent = pad(Math.floor(remaining / 60000) % 60);
                    countdown.querySelector('[data-countdown-seconds]').textContent = pad(Math.floor(remaining / 1000) % 60);
                };

                clock.hidden = false;
                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endif

    <script>
        (() => {
            const sections = document.querySelectorAll('[data-reveal]');

            if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { rootMargin: '0px 0px -12% 0px' });

            sections.forEach((section) => {
                section.classList.add('duyog-reveal');
                observer.observe(section);
            });
        })();
    </script>

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
