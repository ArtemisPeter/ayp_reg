<?php

namespace App\Http\Controllers;

use App\CampId\QrCodeSvg;
use App\Models\Attendee;
use App\Models\Circuit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CampIdController extends Controller
{
    public function index()
    {
        $circuits = Circuit::withCount(['attendees as pending_count' => function ($query) {
            $query->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
                ->where(fn ($pending) => $pending->whereNull('printed_at')->orWhere('needs_reprint', true));
        }])->orderBy('name')->get();

        return view('campids.index', ['circuits' => $circuits]);
    }

    public function fronts(Circuit $circuit)
    {
        return view('campids.fronts', [
            'circuit' => $circuit,
            'attendees' => $this->pendingFor($circuit),
        ]);
    }

    public function backs(Circuit $circuit, QrCodeSvg $qr)
    {
        // Each row of two is reversed so a double-sided print lines up with the fronts.
        $sheets = $this->pendingFor($circuit)
            ->chunk(4)
            ->map(fn ($sheet) => $sheet
                ->chunk(2)
                ->flatMap(fn ($row) => $row->pad(2, null)->reverse()->values())
                ->map(fn (?Attendee $attendee) => $attendee === null ? null : $qr->render($attendee->qr_code)));

        return view('campids.backs', ['circuit' => $circuit, 'sheets' => $sheets]);
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
     * @return Collection<int, Attendee>
     */
    private function pendingFor(Circuit $circuit): Collection
    {
        return Attendee::with('localChurch.circuit')
            ->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
            ->whereHas('localChurch', fn ($church) => $church->where('circuit_id', $circuit->id))
            ->where(fn ($query) => $query->whereNull('printed_at')->orWhere('needs_reprint', true))
            ->orderBy('local_church_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}
