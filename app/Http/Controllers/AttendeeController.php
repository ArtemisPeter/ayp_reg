<?php

namespace App\Http\Controllers;

use App\Enums\AttendeeType;
use App\Enums\RegistrationType;
use App\Enums\Role;
use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\YouthCamp;
use App\Registration\RosterImport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendeeController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request)
    {
        $filters = $this->rosterFilters($request);
        $circuitId = $this->circuitPresidentCircuitId($request);

        return view('attendees.index', [
            'attendees' => $this->rosterQuery($request, $filters)->paginate(self::PER_PAGE)->withQueryString(),
            'search' => $filters['search'],
            'filters' => $filters,
            'hasFilters' => $this->hasFilters($filters),
            'camp' => YouthCamp::where('is_current', true)->first(),
            'churches' => LocalChurch::with('circuit')->orderBy('name')->get()->sortBy('circuit.name'),
            'filterCircuits' => Circuit::orderBy('name')
                ->when($circuitId, fn ($query, int $id) => $query->whereKey($id))
                ->get(),
            'filterChurches' => LocalChurch::with('circuit')
                ->when($circuitId, fn ($query, int $id) => $query->where('circuit_id', $id))
                ->orderBy('name')
                ->get()
                ->sortBy('circuit.name'),
        ]);
    }

    /**
     * Downloads the roster as it is filtered on screen, in the upload template's columns so the
     * file can be corrected and uploaded again.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->rosterQuery($request, $this->rosterFilters($request));

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [...RosterImport::COLUMNS, 'Registration']);

            foreach ($query->lazy() as $attendee) {
                fputcsv($out, array_map($this->safeForSpreadsheet(...), [
                    $attendee->first_name,
                    (string) $attendee->middle_name,
                    $attendee->last_name,
                    $attendee->localChurch->name,
                    $attendee->localChurch->circuit->name,
                    $attendee->birthday->format('m/d/Y'),
                    $attendee->is_baptized ? 'Yes' : 'No',
                    $attendee->type === AttendeeType::Pastor ? 'P' : 'Y',
                    $attendee->registration_type === RegistrationType::PreRegistration ? 'Pre-Registration' : 'On-Site',
                ]));
            }

            fclose($out);
        }, 'attendees-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * A print-ready page of the filtered roster, saved as a PDF from the browser's print dialog.
     */
    public function exportPdf(Request $request)
    {
        $filters = $this->rosterFilters($request);

        return view('attendees.print', [
            'attendees' => $this->rosterQuery($request, $filters)->get(),
            'camp' => YouthCamp::where('is_current', true)->first(),
            'filterSummary' => $this->filterSummary($filters),
        ]);
    }

    /**
     * The roster filters from the query string, with anything unrecognised dropped.
     *
     * @return array{search: string, circuit: ?int, church: ?int, baptismal: ?string, type: ?string, registration: ?string}
     */
    private function rosterFilters(Request $request): array
    {
        $choice = fn (string $key, array $allowed): ?string => in_array($request->query($key), $allowed, true) ? $request->query($key) : null;
        $id = fn (string $key): ?int => ctype_digit((string) $request->query($key)) ? (int) $request->query($key) : null;

        return [
            'search' => trim((string) $request->query('search')),
            'circuit' => $id('circuit'),
            'church' => $id('church'),
            'baptismal' => $choice('baptismal', ['yes', 'no']),
            'type' => $choice('type', array_column(AttendeeType::cases(), 'value')),
            'registration' => $choice('registration', array_column(RegistrationType::cases(), 'value')),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function hasFilters(array $filters): bool
    {
        return collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
    }

    /**
     * Current-camp Attendees the user may see, narrowed by the search and filters, in roster order.
     *
     * @param  array<string, mixed>  $filters
     */
    private function rosterQuery(Request $request, array $filters): Builder
    {
        return Attendee::with('localChurch.circuit')
            ->whereHas('youthCamp', fn ($camp) => $camp->where('is_current', true))
            ->when(
                $request->user()->role === Role::CircuitPresident,
                fn ($query) => $query->whereHas('localChurch', fn ($church) => $church->where('circuit_id', $request->user()->circuit_id)),
            )
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                foreach (preg_split('/\s+/', $filters['search']) as $word) {
                    $query->where(fn ($match) => $this->matchesSearchWord($match, $word));
                }
            })
            ->when($filters['circuit'], fn ($query, int $id) => $query->whereHas('localChurch', fn ($church) => $church->where('circuit_id', $id)))
            ->when($filters['church'], fn ($query, int $id) => $query->where('local_church_id', $id))
            ->when($filters['baptismal'], fn ($query, string $value) => $query->where('is_baptized', $value === 'yes'))
            ->when($filters['type'], fn ($query, string $value) => $query->where('type', $value))
            ->when($filters['registration'], fn ($query, string $value) => $query->where('registration_type', $value))
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    /**
     * The active filters in words, for the heading of the printed roster.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, string>
     */
    private function filterSummary(array $filters): array
    {
        return array_values(array_filter([
            $filters['circuit'] ? 'Circuit: '.Circuit::find($filters['circuit'])?->name : null,
            $filters['church'] ? 'Local Church: '.LocalChurch::find($filters['church'])?->name : null,
            $filters['baptismal'] ? 'Baptismal: '.ucfirst($filters['baptismal']) : null,
            $filters['type'] ? 'Type: '.ucfirst($filters['type']) : null,
            $filters['registration'] ? 'Registration: '.($filters['registration'] === 'pre_registration' ? 'Pre-Registration' : 'On-Site') : null,
            $filters['search'] !== '' ? "Search: \"{$filters['search']}\"" : null,
        ]));
    }

    /**
     * Stops a spreadsheet from running a cell that starts like a formula.
     */
    private function safeForSpreadsheet(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
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
        $this->ensureCanEdit($request, $attendee);

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

    public function edit(Request $request, Attendee $attendee)
    {
        $this->ensureCanEdit($request, $attendee);

        return view('attendees.edit', [
            'attendee' => $attendee->load('localChurch.circuit'),
            'churches' => LocalChurch::with('circuit')
                ->when($this->circuitPresidentCircuitId($request), fn ($query, int $circuitId) => $query->where('circuit_id', $circuitId))
                ->orderBy('name')
                ->get()
                ->sortBy('circuit.name'),
        ]);
    }

    /**
     * Matches one search word against any part of an Attendee's row: their names, Local Church,
     * Circuit and birthday, or the Baptismal, Type and Registration columns by the words shown in them.
     */
    private function matchesSearchWord(Builder $query, string $word): Builder
    {
        $like = '%'.addcslashes($word, '%_\\').'%';
        $keyword = mb_strtolower(trim($word, '.,'));

        $query->where('first_name', 'like', $like)
            ->orWhere('middle_name', 'like', $like)
            ->orWhere('last_name', 'like', $like)
            ->orWhere('birthday', 'like', $like)
            ->orWhereHas('localChurch', fn ($church) => $church->where('name', 'like', $like)
                ->orWhereHas('circuit', fn ($circuit) => $circuit->where('name', 'like', $like)));

        $columnValues = [
            'yes' => ['is_baptized', true],
            'baptized' => ['is_baptized', true],
            'baptised' => ['is_baptized', true],
            'no' => ['is_baptized', false],
            'unbaptized' => ['is_baptized', false],
            'youth' => ['type', AttendeeType::Youth],
            'pastor' => ['type', AttendeeType::Pastor],
            'pastors' => ['type', AttendeeType::Pastor],
            'pre' => ['registration_type', RegistrationType::PreRegistration],
            'pre-reg' => ['registration_type', RegistrationType::PreRegistration],
            'pre-registration' => ['registration_type', RegistrationType::PreRegistration],
            'preregistration' => ['registration_type', RegistrationType::PreRegistration],
            'on-site' => ['registration_type', RegistrationType::OnSite],
            'onsite' => ['registration_type', RegistrationType::OnSite],
            'walk-in' => ['registration_type', RegistrationType::OnSite],
        ];

        if (isset($columnValues[$keyword])) {
            [$column, $value] = $columnValues[$keyword];
            $query->orWhere($column, $value);
        }

        return $query;
    }

    /**
     * A Circuit President may edit only current-camp Attendees of their own Circuit.
     */
    private function ensureCanEdit(Request $request, Attendee $attendee): void
    {
        if ($request->user()->role !== Role::CircuitPresident) {
            return;
        }

        $circuitId = $request->user()->circuit_id;
        $attendee->loadMissing('localChurch', 'youthCamp');

        abort_unless(
            $circuitId !== null
                && $attendee->localChurch?->circuit_id === $circuitId
                && $attendee->youthCamp?->is_current,
            403,
        );
    }

    /**
     * The Circuit a Circuit President is limited to, or null for staff who see every Circuit.
     */
    private function circuitPresidentCircuitId(Request $request): ?int
    {
        return $request->user()->role === Role::CircuitPresident ? $request->user()->circuit_id : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $circuitId = $this->circuitPresidentCircuitId($request);

        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'local_church_id' => [
                'required',
                Rule::exists('local_churches', 'id')
                    ->whereNull('deleted_at')
                    ->when($circuitId !== null, fn ($rule) => $rule->where('circuit_id', $circuitId)),
            ],
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
