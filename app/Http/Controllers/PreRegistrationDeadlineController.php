<?php

namespace App\Http\Controllers;

use App\Models\YouthCamp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PreRegistrationDeadlineController extends Controller
{
    /**
     * Sets, or clears when left empty, the last day Circuit Presidents may upload pre-registration rosters.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pre_registration_deadline' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $camp = YouthCamp::where('is_current', true)->first();

        if ($camp === null) {
            return redirect('/attendees')->withErrors(['pre_registration_deadline' => 'No Youth Camp is current yet. Ask the Admin to set the current camp.']);
        }

        $camp->update(['pre_registration_deadline' => $data['pre_registration_deadline'] ?? null]);

        return redirect('/attendees');
    }
}
