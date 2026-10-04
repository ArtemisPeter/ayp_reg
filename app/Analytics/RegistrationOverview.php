<?php

namespace App\Analytics;

use App\Enums\AttendeeType;
use App\Enums\RegistrationType;
use App\Models\Attendee;
use App\Models\Circuit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RegistrationOverview
{
    /**
     * Registration totals for the current camp, optionally scoped to one Circuit.
     *
     * @return array{total: int, youth: int, pastors: int, preRegistration: int, onSite: int, baptized: int, notBaptized: int}
     */
    public function summary(?Circuit $circuit = null): array
    {
        $counts = $this->currentCampAttendees($circuit)
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('coalesce(sum(case when type = ? then 1 else 0 end), 0) as youth', [AttendeeType::Youth->value])
            ->selectRaw('coalesce(sum(case when type = ? then 1 else 0 end), 0) as pastors', [AttendeeType::Pastor->value])
            ->selectRaw('coalesce(sum(case when registration_type = ? then 1 else 0 end), 0) as pre_registration', [RegistrationType::PreRegistration->value])
            ->selectRaw('coalesce(sum(case when registration_type = ? then 1 else 0 end), 0) as on_site', [RegistrationType::OnSite->value])
            ->selectRaw('coalesce(sum(case when is_baptized = 1 then 1 else 0 end), 0) as baptized')
            ->first();

        return [
            'total' => (int) $counts->total,
            'youth' => (int) $counts->youth,
            'pastors' => (int) $counts->pastors,
            'preRegistration' => (int) $counts->pre_registration,
            'onSite' => (int) $counts->on_site,
            'baptized' => (int) $counts->baptized,
            'notBaptized' => (int) $counts->total - (int) $counts->baptized,
        ];
    }

    /**
     * One row per Circuit: how many are registered, the Pre-Registration/On-Site split, and how
     * many Camp IDs are printed versus still waiting (not yet printed, or flagged for reprint).
     *
     * @return Collection<int, array{circuit: string, total: int, preRegistration: int, onSite: int, printed: int, pending: int}>
     */
    public function perCircuit(): Collection
    {
        $countsByCircuit = $this->currentCampAttendees(null)
            ->toBase()
            ->join('local_churches', 'local_churches.id', '=', 'attendees.local_church_id')
            ->whereNull('local_churches.deleted_at')
            ->groupBy('local_churches.circuit_id')
            ->select('local_churches.circuit_id')
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when attendees.registration_type = ? then 1 else 0 end) as pre_registration', [RegistrationType::PreRegistration->value])
            ->selectRaw('sum(case when attendees.registration_type = ? then 1 else 0 end) as on_site', [RegistrationType::OnSite->value])
            ->selectRaw('sum(case when attendees.printed_at is not null and attendees.needs_reprint = 0 then 1 else 0 end) as printed')
            ->get()
            ->keyBy('circuit_id');

        return Circuit::orderBy('name')->get()->map(function (Circuit $circuit) use ($countsByCircuit) {
            $counts = $countsByCircuit->get($circuit->id);
            $total = (int) ($counts->total ?? 0);
            $printed = (int) ($counts->printed ?? 0);

            return [
                'circuit' => $circuit->name,
                'total' => $total,
                'preRegistration' => (int) ($counts->pre_registration ?? 0),
                'onSite' => (int) ($counts->on_site ?? 0),
                'printed' => $printed,
                'pending' => $total - $printed,
            ];
        });
    }

    /**
     * @return Builder<Attendee>
     */
    private function currentCampAttendees(?Circuit $circuit): Builder
    {
        return Attendee::whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
            ->when(
                $circuit !== null,
                fn ($query) => $query->whereHas('localChurch', fn ($church) => $church->where('circuit_id', $circuit->id)),
            );
    }
}
