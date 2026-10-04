<?php

namespace App\Portal;

use App\Enums\AttendeeType;
use App\Models\Circuit;
use Illuminate\Support\Collection;

class YouthCountLeaderboard
{
    /**
     * Every Circuit with its number of Youth in the current camp, most first. Circuits with the
     * same count share a rank, are ordered alphabetically and are marked as tied. Circuits with no
     * Youth yet are neither ranked nor marked.
     *
     * @return Collection<int, array{circuit: string, youth: int, rank: int|null, tie: bool}>
     */
    public function rows(): Collection
    {
        $circuits = Circuit::withCount(['attendees as youth' => function ($attendees) {
            $attendees->where('type', AttendeeType::Youth->value)
                ->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true));
        }])
            ->get()
            ->sortBy([['youth', 'desc'], ['name', 'asc']])
            ->values()
            ->map(fn (Circuit $circuit) => ['circuit' => $circuit->name, 'youth' => $circuit->youth]);

        return TiedStandings::apply($circuits, 'youth');
    }
}
