<?php

namespace App\Portal;

use App\Models\Circuit;
use App\Models\EventScore;
use App\Models\YouthCamp;
use Illuminate\Support\Collection;

class ContestLeaderboard
{
    /**
     * Every Circuit with its total contest points in the current camp, most first. Circuits with the
     * same total share a rank, are ordered alphabetically and are marked as tied. Circuits with zero
     * points are neither ranked nor marked.
     *
     * @return Collection<int, array{circuit: string, points: int, rank: int|null, tie: bool}>
     */
    public function rows(): Collection
    {
        $campId = YouthCamp::where('is_current', true)->value('id');

        $pointsByCircuit = EventScore::with('rank')
            ->whereHas('event', fn ($event) => $event->where('youth_camp_id', $campId))
            ->get()
            ->groupBy('circuit_id')
            ->map(fn (Collection $scores) => $scores->sum(fn (EventScore $score) => $score->rank->points));

        $circuits = Circuit::orderBy('name')->get()
            ->map(fn (Circuit $circuit) => ['circuit' => $circuit->name, 'points' => $pointsByCircuit->get($circuit->id, 0)])
            ->sortBy([['points', 'desc'], ['circuit', 'asc']])
            ->values();

        return TiedStandings::apply($circuits, 'points');
    }
}
