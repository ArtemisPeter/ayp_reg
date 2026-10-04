<?php

namespace App\Portal;

use Illuminate\Support\Collection;

class TiedStandings
{
    /**
     * Adds 'rank' and 'tie' to each row of a collection already sorted highest value first, then
     * alphabetically by 'circuit'. Rows with equal values share a rank and are marked tied. A row
     * with a value of zero is left unranked and unmarked, since zero usually means "hasn't started"
     * rather than a real last place.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  string  $key  the numeric field to rank by
     * @return Collection<int, array<string, mixed>>
     */
    public static function apply(Collection $rows, string $key): Collection
    {
        $values = $rows->pluck($key);

        return $rows->map(fn (array $row) => $row + [
            'rank' => $row[$key] === 0 ? null : 1 + $values->filter(fn (int $value) => $value > $row[$key])->count(),
            'tie' => $row[$key] > 0 && $values->filter(fn (int $value) => $value === $row[$key])->count() > 1,
        ]);
    }
}
