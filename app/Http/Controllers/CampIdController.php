<?php

namespace App\Http\Controllers;

use App\CampId\QrCodeSvg;
use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampIdController extends Controller
{
    public function index()
    {
        $circuits = Circuit::withCount(['attendees as pending_count' => fn ($query) => $this->onlyPending($query)])
            ->orderBy('name')
            ->get();

        $pendingByChurch = Attendee::query()
            ->tap(fn ($query) => $this->onlyPending($query))
            ->selectRaw('local_church_id, count(*) as pending_count')
            ->groupBy('local_church_id')
            ->pluck('pending_count', 'local_church_id');

        $churchesByCircuit = LocalChurch::whereIn('id', $pendingByChurch->keys())
            ->orderBy('name')
            ->get()
            ->each(fn (LocalChurch $church) => $church->pending_count = $pendingByChurch[$church->id])
            ->groupBy('circuit_id');

        return view('campids.index', ['circuits' => $circuits, 'churchesByCircuit' => $churchesByCircuit]);
    }

    public function fronts(Request $request, Circuit $circuit)
    {
        $church = $this->churchFilter($request, $circuit);

        return view('campids.fronts', [
            'circuit' => $circuit,
            'church' => $church,
            'attendees' => $this->pendingFor($circuit, $church),
        ]);
    }

    public function backs(Request $request, Circuit $circuit, QrCodeSvg $qr)
    {
        $church = $this->churchFilter($request, $circuit);

        // Each row of two is reversed so a double-sided print lines up with the fronts.
        $sheets = $this->pendingFor($circuit, $church)
            ->chunk(4)
            ->map(fn ($sheet) => $sheet
                ->chunk(2)
                ->flatMap(fn ($row) => $row->pad(2, null)->reverse()->values())
                ->map(fn (?Attendee $attendee) => $attendee === null ? null : $qr->render($attendee->qr_code)));

        return view('campids.backs', ['circuit' => $circuit, 'church' => $church, 'sheets' => $sheets]);
    }

    public function single(Attendee $attendee, QrCodeSvg $qr)
    {
        $attendee->load('localChurch.circuit');

        return view('campids.single', [
            'attendee' => $attendee,
            'circuit' => $attendee->localChurch->circuit,
            'qrSvg' => $qr->render($attendee->qr_code),
        ]);
    }

    public function markPrinted(Request $request, Circuit $circuit): RedirectResponse
    {
        $ids = $request->validate([
            'attendee_ids' => ['required', 'array'],
            'attendee_ids.*' => ['integer'],
        ])['attendee_ids'];

        $this->pendingFor($circuit)
            ->whereIn('id', $ids)
            ->each(fn (Attendee $attendee) => $attendee->forceFill(['printed_at' => now(), 'needs_reprint' => false])->save());

        return redirect('/camp-ids');
    }

    /**
     * The Local Church named by the optional `church` query parameter, which must belong to the Circuit.
     */
    private function churchFilter(Request $request, Circuit $circuit): ?LocalChurch
    {
        if (! $request->filled('church')) {
            return null;
        }

        return LocalChurch::where('circuit_id', $circuit->id)->findOrFail($request->integer('church'));
    }

    /**
     * @return Collection<int, Attendee>
     */
    private function pendingFor(Circuit $circuit, ?LocalChurch $church = null): Collection
    {
        return Attendee::with('localChurch.circuit')
            ->tap(fn ($query) => $this->onlyPending($query))
            ->whereHas('localChurch', fn ($query) => $query->where('circuit_id', $circuit->id))
            ->when($church, fn ($query) => $query->where('local_church_id', $church->id))
            ->orderBy('local_church_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Narrows to current-camp attendees whose Camp ID has not been printed or needs reprinting.
     */
    private function onlyPending(Builder $query): Builder
    {
        return $query->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
            ->where(fn ($pending) => $pending->whereNull('printed_at')->orWhere('needs_reprint', true));
    }
}
