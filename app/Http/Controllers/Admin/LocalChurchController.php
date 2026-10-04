<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocalChurchController extends Controller
{
    public function index(Circuit $circuit)
    {
        return view('admin.churches.index', [
            'circuit' => $circuit,
            'churches' => $circuit->localChurches()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Circuit $circuit): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('local_churches', 'name')->withoutTrashed()->where('circuit_id', $circuit->id),
            ],
        ]);

        $circuit->localChurches()->create($data);

        return redirect("/admin/circuits/{$circuit->id}/churches");
    }

    public function update(Request $request, Circuit $circuit, LocalChurch $localChurch): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('local_churches', 'name')->withoutTrashed()
                    ->where('circuit_id', $circuit->id)->ignore($localChurch->id),
            ],
        ]);

        $localChurch->update($data);

        return redirect("/admin/circuits/{$circuit->id}/churches");
    }

    public function destroy(Circuit $circuit, LocalChurch $localChurch): RedirectResponse
    {
        if (Attendee::where('local_church_id', $localChurch->id)->exists()) {
            return redirect("/admin/circuits/{$circuit->id}/churches")->withErrors(['name' => 'This church has Attendees registered and cannot be removed.']);
        }

        $localChurch->delete();

        return redirect("/admin/circuits/{$circuit->id}/churches");
    }
}
