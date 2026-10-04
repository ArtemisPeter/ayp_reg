<?php

namespace App\Registration;

use App\Enums\AttendeeType;
use App\Enums\RegistrationType;
use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\YouthCamp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RosterImport
{
    public const COLUMNS = [
        'First Name', 'Middle Name', 'Last Name', 'Church Name', 'Circuit Name', 'Birthday', 'Baptismal', 'Type',
    ];

    /**
     * Registers every row of the file, or nothing at all when any row is invalid.
     *
     * @param  Circuit|null  $onlyCircuit  When given, every row must belong to this Circuit.
     * @return array<int, array{row: int, messages: array<int, string>}> The problems found, empty when the file was accepted.
     */
    public function import(UploadedFile $file, YouthCamp $camp, ?Circuit $onlyCircuit, RegistrationType $registrationType): array
    {
        ['rows' => $rows, 'problems' => $problems] = $this->readRows($file);
        $circuits = $this->circuitsByName($onlyCircuit);

        $firstSeenOnRow = [];
        foreach ($rows as $line => $row) {
            $messages = $this->problemsWith($row, $circuits, $onlyCircuit);

            $person = $this->personKey($row);
            if (isset($firstSeenOnRow[$person])) {
                $messages[] = "Same person as row {$firstSeenOnRow[$person]}";
            } else {
                $firstSeenOnRow[$person] = $line;
            }

            if ($messages !== []) {
                $problems[$line] = $messages;
            }
        }

        if ($problems !== []) {
            ksort($problems);

            return collect($problems)
                ->map(fn (array $messages, int $line) => ['row' => $line, 'messages' => $messages])
                ->values()
                ->all();
        }

        DB::transaction(function () use ($rows, $camp, $circuits, $registrationType): void {
            foreach ($rows as $row) {
                $churchId = $circuits[$this->normalise($row['Circuit Name'])]['churches'][$this->normalise($row['Church Name'])];

                $attendee = Attendee::firstOrNew([
                    'youth_camp_id' => $camp->id,
                    'local_church_id' => $churchId,
                    'first_name' => $row['First Name'],
                    'last_name' => $row['Last Name'],
                ]);

                $attendee->fill([
                    'middle_name' => $row['Middle Name'] ?: null,
                    'birthday' => $this->toStorageDate($row['Birthday']),
                    'is_baptized' => $this->normalise($row['Baptismal']) === 'yes',
                    'type' => $this->normalise($row['Type']) === 'p' ? AttendeeType::Pastor : AttendeeType::Youth,
                ]);
                $attendee->registration_type ??= $registrationType;
                $attendee->save();
            }
        });

        return [];
    }

    /**
     * @return array{rows: array<int, array<string, string>>, problems: array<int, array<int, string>>}
     *                                                                                                  Trimmed data rows and problems, both keyed by their line number in the file.
     */
    private function readRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle) ?: [];

        if ($header !== []) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }
        $header = array_map(fn ($name) => trim((string) $name), $header);

        $missing = array_values(array_diff(self::COLUMNS, $header));
        if ($missing !== []) {
            fclose($handle);

            return ['rows' => [], 'problems' => [1 => array_map(fn (string $name) => "Missing column: {$name}", $missing)]];
        }

        $rows = [];
        $problems = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;

            if ($values === [null] || $values === []) {
                continue;
            }

            if (count($values) !== count($header)) {
                $problems[$line] = ['Expected '.count($header).' values but found '.count($values)];

                continue;
            }

            $rows[$line] = array_combine($header, array_map('trim', $values));
        }

        fclose($handle);

        if ($rows === [] && $problems === []) {
            $problems[1] = ['The file has no rows to register'];
        }

        return ['rows' => $rows, 'problems' => $problems];
    }

    /**
     * @return array<string, array{name: string, churches: array<string, int>}>
     */
    private function circuitsByName(?Circuit $onlyCircuit): array
    {
        $circuits = $onlyCircuit === null ? Circuit::with('localChurches')->get() : collect([$onlyCircuit->load('localChurches')]);

        return $circuits
            ->mapWithKeys(fn (Circuit $circuit) => [
                $this->normalise($circuit->name) => [
                    'name' => $circuit->name,
                    'churches' => $circuit->localChurches
                        ->mapWithKeys(fn ($church) => [$this->normalise($church->name) => $church->id])
                        ->all(),
                ],
            ])
            ->all();
    }

    /**
     * @param  array<string, string>  $row
     * @param  array<string, array{name: string, churches: array<string, int>}>  $circuits
     * @return array<int, string>
     */
    private function problemsWith(array $row, array $circuits, ?Circuit $onlyCircuit): array
    {
        $messages = [];

        foreach (['First Name', 'Last Name'] as $required) {
            if ($row[$required] === '') {
                $messages[] = "{$required} is required";
            }
        }

        $circuitKey = $this->normalise($row['Circuit Name']);

        if ($onlyCircuit !== null && $circuitKey !== $this->normalise($onlyCircuit->name)) {
            $messages[] = "Circuit Name must be {$onlyCircuit->name}";
        } elseif (! isset($circuits[$circuitKey])) {
            $messages[] = "Circuit \"{$row['Circuit Name']}\" does not exist";
        } elseif (! isset($circuits[$circuitKey]['churches'][$this->normalise($row['Church Name'])])) {
            $messages[] = "Church \"{$row['Church Name']}\" is not a Local Church of {$circuits[$circuitKey]['name']}";
        }

        if (! $this->isValidDate($row['Birthday'])) {
            $messages[] = 'Birthday must look like 04/03/2008';
        }

        if (! in_array($this->normalise($row['Baptismal']), ['yes', 'no'], true)) {
            $messages[] = 'Baptismal must be Yes or No';
        }

        if (! in_array($this->normalise($row['Type']), ['y', 'p'], true)) {
            $messages[] = 'Type must be Y or P';
        }

        return $messages;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function personKey(array $row): string
    {
        return implode('|', array_map(
            $this->normalise(...),
            [$row['First Name'], $row['Last Name'], $row['Church Name'], $row['Circuit Name']],
        ));
    }

    private function normalise(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function isValidDate(string $value): bool
    {
        return preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $parts) === 1
            && checkdate((int) $parts[1], (int) $parts[2], (int) $parts[3]);
    }

    /**
     * Excel drops leading zeros when saving a CSV (6/28/2014), so both padded and unpadded
     * months and days are accepted.
     */
    private function toStorageDate(string $mmddyyyy): string
    {
        [$month, $day, $year] = explode('/', $mmddyyyy);

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}
