<?php

namespace App\Http\Controllers;

use App\Enums\AttendeeType;
use App\Enums\RegistrationType;
use App\Enums\Role;
use App\Models\Attendee;
use App\Models\LocalChurch;
use App\Models\YouthCamp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendeeController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $attendees = Attendee::with('localChurch.circuit')
            ->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
            ->when(
                $request->user()->role === Role::CircuitPresident,
                fn ($query) => $query->whereHas('localChurch', fn ($church) => $church->where('circuit_id', $request->user()->circuit_id)),
            )
            ->when($search !== '', function ($query) use ($search) {
                foreach (preg_split('/\s+/', $search) as $word) {
                    $like = '%'.addcslashes($word, '%_\\').'%';

                    $query->where(fn ($name) => $name->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like));
                }
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('attendees.index', [
            'attendees' => $attendees,
            'search' => $search,
            'churches' => LocalChurch::with('circuit')->orderBy('name')->get()->sortBy('circuit.name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $camp = YouthCamp::where('is_current', true)->first();

        if ($camp === null) {
            return redirect('/attendees')->withErrors(['first_name' => 'No Youth Camp is open for registration yet. Set the current camp first.']);
        }

        if ($this->isAlreadyRegistered($camp->id, $data)) {
            return redirect('/attendees')
                ->withErrors(['first_name' => "{$data['first_name']} {$data['last_name']} is already registered for this church."])
                ->withInput();
        }

        Attendee::create($this->attributesFrom($data) + [
            'youth_camp_id' => $camp->id,
            'registration_type' => RegistrationType::OnSite,
        ]);

        return redirect('/attendees');
    }

    public function update(Request $request, Attendee $attendee): RedirectResponse
    {
        $data = $this->validated($request);

        if ($this->isAlreadyRegistered($attendee->youth_camp_id, $data, ignoring: $attendee->id)) {
            return redirect("/attendees/{$attendee->id}/edit")
                ->withErrors(['first_name' => "{$data['first_name']} {$data['last_name']} is already registered for this church."])
                ->withInput();
        }

        $attendee->fill($this->attributesFrom($data));

        if ($attendee->isDirty('first_name')) {
            $attendee->needs_reprint = true;
        }

        $attendee->save();

        return redirect('/attendees');
    }

    public function destroy(Attendee $attendee): RedirectResponse
    {
        $attendee->delete();

        return redirect('/attendees');
    }

    public function edit(Attendee $attendee)
    {
        return view('attendees.edit', [
            'attendee' => $attendee->load('localChurch.circuit'),
            'churches' => LocalChurch::with('circuit')->orderBy('name')->get()->sortBy('circuit.name'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'local_church_id' => ['required', Rule::exists('local_churches', 'id')->whereNull('deleted_at')],
            'birthday' => ['required', 'date_format:Y-m-d'],
            'baptismal' => ['required', 'in:Yes,No'],
            'type' => ['required', 'in:Y,P'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributesFrom(array $data): array
    {
        return [
            'local_church_id' => $data['local_church_id'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'birthday' => $data['birthday'],
            'is_baptized' => $data['baptismal'] === 'Yes',
            'type' => $data['type'] === 'P' ? AttendeeType::Pastor : AttendeeType::Youth,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isAlreadyRegistered(int $campId, array $data, ?int $ignoring = null): bool
    {
        return Attendee::where('youth_camp_id', $campId)
            ->where('local_church_id', $data['local_church_id'])
            ->where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'])
            ->when($ignoring !== null, fn ($query) => $query->whereKeyNot($ignoring))
            ->exists();
    }
}
