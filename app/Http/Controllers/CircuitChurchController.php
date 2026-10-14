<?php

namespace App\Http\Controllers;

use App\Models\Circuit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A Circuit President's view of their own circuit's Local Churches: they may add a church,
 * but renaming and removing stay with the admin.
 */
class CircuitChurchController extends Controller
{
    public function index(Request $request)
    {
        $circuit = $this->circuitOf($request);

        return view('admin.churches.index', [
            'circuit' => $circuit,
            'churches' => $circuit->localChurches()->orderBy('name')->get(),
            'churchesUrl' => '/churches',
            'canEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $circuit = $this->circuitOf($request);

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('local_churches', 'name')->withoutTrashed()->where('circuit_id', $circuit->id),
            ],
        ]);

        $circuit->localChurches()->create($data);

        return redirect('/churches');
    }

    private function circuitOf(Request $request): Circuit
    {
        $circuit = $request->user()->circuit;

        abort_if($circuit === null, 403);

        return $circuit;
    }
}
