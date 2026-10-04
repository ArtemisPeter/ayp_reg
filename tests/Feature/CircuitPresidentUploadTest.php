<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Circuit;
use App\Models\LocalChurch;
use App\Models\User;
use App\Models\YouthCamp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CircuitPresidentUploadTest extends TestCase
{
    use RefreshDatabase;

    private const HEADER = 'First Name,Middle Name,Last Name,Church Name,Circuit Name,Birthday,Baptismal,Type';

    private Circuit $digos;

    private User $president;

    protected function setUp(): void
    {
        parent::setUp();

        YouthCamp::factory()->create(['year' => 2026, 'is_current' => true]);

        $this->digos = Circuit::factory()->create(['name' => 'Digos City']);
        LocalChurch::factory()->create(['circuit_id' => $this->digos->id, 'name' => 'Grace Church']);
        LocalChurch::factory()->create(['circuit_id' => $this->digos->id, 'name' => 'Faith Church']);

        $this->president = User::factory()->create([
            'role' => 'circuit_president',
            'circuit_id' => $this->digos->id,
        ]);
    }

    /**
     * @param  array<int, string>  $rows
     */
    private function csv(array $rows): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'roster.csv',
            self::HEADER."\n".implode("\n", $rows)."\n",
        );
    }

    public function test_a_valid_file_registers_every_row_and_the_attendees_appear_in_the_presidents_list(): void
    {
        $file = $this->csv([
            'Juan,Dela,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y',
            'Maria,,Santos,Faith Church,Digos City,11/20/2001,No,Y',
        ]);

        $this->actingAs($this->president)
            ->post('/registrations/upload', ['file' => $file])
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('Juan')
            ->assertSee('Cruz')
            ->assertSee('Maria')
            ->assertSee('Santos');
    }

    public function test_a_birthday_without_leading_zeros_as_saved_by_excel_is_accepted(): void
    {
        $this->actingAs($this->president)
            ->post('/registrations/upload', ['file' => $this->csv(['Juan,,Cruz,Grace Church,Digos City,6/8/2014,Yes,Y'])])
            ->assertRedirect('/attendees');

        $this->assertDatabaseHas('attendees', ['first_name' => 'Juan']);
        $this->assertSame('2014-06-08', Attendee::where('first_name', 'Juan')->value('birthday')->toDateString());
    }

    public function test_one_bad_row_rejects_the_whole_file_and_names_the_row_and_the_reason(): void
    {
        $file = $this->csv([
            'Juan,Dela,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y',
            'Maria,,Santos,Faith Church,Digos City,2001-03-04,No,Y',
        ]);

        $this->actingAs($this->president)
            ->from('/attendees')
            ->post('/registrations/upload', ['file' => $file])
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('Row 3')
            ->assertSee('Birthday must look like 04/03/2008')
            ->assertViewHas('attendees', fn ($attendees) => $attendees->isEmpty());
    }

    public function test_every_bad_row_is_listed_and_good_rows_are_not(): void
    {
        $file = $this->csv([
            'Juan,,Cruz,Grace Church,Digos City,nope,Yes,Y',
            'Ana,,Reyes,Grace Church,Digos City,01/15/2005,Yes,Y',
            'Pedro,,Lim,Grace Church,Digos City,06/30/2004,Maybe,Y',
        ]);

        $this->actingAs($this->president)
            ->from('/attendees')
            ->post('/registrations/upload', ['file' => $file]);

        $this->get('/attendees')
            ->assertSee('Row 2')
            ->assertDontSee('Row 3')
            ->assertSee('Row 4');
    }

    public function test_the_same_person_twice_in_one_file_is_rejected(): void
    {
        $file = $this->csv([
            'Juan,,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y',
            'Ana,,Reyes,Grace Church,Digos City,01/15/2005,Yes,Y',
            'juan ,,CRUZ,grace church,Digos City,04/03/2008,Yes,Y',
        ]);

        $this->actingAs($this->president)
            ->from('/attendees')
            ->post('/registrations/upload', ['file' => $file]);

        $this->get('/attendees')
            ->assertSee('Row 4')
            ->assertSee('Same person as row 2')
            ->assertViewHas('attendees', fn ($attendees) => $attendees->isEmpty());
    }

    public function test_uploading_a_corrected_file_updates_the_same_person_instead_of_adding_another(): void
    {
        $this->actingAs($this->president)->post('/registrations/upload', [
            'file' => $this->csv(['Juan,Dela,Cruz,Grace Church,Digos City,04/03/2008,No,Y']),
        ]);

        $this->post('/registrations/upload', [
            'file' => $this->csv(['juan,Dela,cruz,Grace Church,Digos City,04/04/2008,Yes,P']),
        ])->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('2008-04-04')
            ->assertDontSee('2008-04-03')
            ->assertViewHas('attendees', function ($attendees) {
                $juan = $attendees->sole();

                return $juan->is_baptized && $juan->type->value === 'pastor' && $juan->registration_type->value === 'pre_registration';
            });
    }

    public function test_a_president_whose_circuit_was_removed_cannot_upload_at_all(): void
    {
        $this->digos->delete();

        $this->actingAs($this->president)
            ->post('/registrations/upload', ['file' => $this->csv(['Juan,,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y'])])
            ->assertForbidden();

        $this->assertDatabaseCount('attendees', 0);
    }

    public function test_an_upload_is_refused_when_no_youth_camp_is_current(): void
    {
        YouthCamp::query()->delete();

        $this->actingAs($this->president)
            ->from('/attendees')
            ->post('/registrations/upload', [
                'file' => $this->csv(['Juan,,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y']),
            ])
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee('No Youth Camp is open for registration yet');
    }

    public function test_a_file_saved_by_excel_with_a_byte_order_mark_is_accepted(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'roster.csv',
            "\xEF\xBB\xBF".self::HEADER."\r\nJuan,Dela,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y\r\n",
        );

        $this->actingAs($this->president)->post('/registrations/upload', ['file' => $file]);

        $this->get('/attendees')->assertSee('Juan');
    }

    #[DataProvider('malformedFiles')]
    public function test_a_malformed_file_is_rejected_with_a_clear_message(string $content, string $reason): void
    {
        $this->actingAs($this->president)
            ->from('/attendees')
            ->post('/registrations/upload', ['file' => UploadedFile::fake()->createWithContent('roster.csv', $content)])
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee($reason)
            ->assertViewHas('attendees', fn ($attendees) => $attendees->isEmpty());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function malformedFiles(): array
    {
        $row = "Juan,,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y\n";

        return [
            'missing column' => [
                "First Name,Middle Name,Last Name,Church Name,Circuit Name,Baptismal,Type\n".$row,
                'Missing column: Birthday',
            ],
            'row with too few values' => [
                self::HEADER."\nJuan,,Cruz,Grace Church\n",
                'Expected 8 values but found 4',
            ],
            'header only' => [
                self::HEADER."\n",
                'The file has no rows to register',
            ],
        ];
    }

    #[DataProvider('badRows')]
    public function test_each_kind_of_bad_value_is_rejected_with_a_clear_reason(string $row, string $reason): void
    {
        $this->actingAs($this->president)
            ->from('/attendees')
            ->post('/registrations/upload', ['file' => $this->csv([$row])])
            ->assertRedirect('/attendees');

        $this->get('/attendees')
            ->assertSee($reason)
            ->assertViewHas('attendees', fn ($attendees) => $attendees->isEmpty());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function badRows(): array
    {
        return [
            'impossible date' => ['Juan,,Cruz,Grace Church,Digos City,02/30/2008,Yes,Y', 'Birthday must look like 04/03/2008'],
            'baptismal not yes or no' => ['Juan,,Cruz,Grace Church,Digos City,04/03/2008,Maybe,Y', 'Baptismal must be Yes or No'],
            'type not Y or P' => ['Juan,,Cruz,Grace Church,Digos City,04/03/2008,Yes,X', 'Type must be Y or P'],
            'unknown church' => ['Juan,,Cruz,Nowhere Church,Digos City,04/03/2008,Yes,Y', 'Church "Nowhere Church" is not a Local Church of Digos City'],
            'another circuit' => ['Juan,,Cruz,Grace Church,Davao City,04/03/2008,Yes,Y', 'Circuit Name must be Digos City'],
            'missing first name' => [',,Cruz,Grace Church,Digos City,04/03/2008,Yes,Y', 'First Name is required'],
            'missing last name' => ['Juan,,,Grace Church,Digos City,04/03/2008,Yes,Y', 'Last Name is required'],
        ];
    }
}
