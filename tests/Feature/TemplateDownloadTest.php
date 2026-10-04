<?php

namespace Tests\Feature;

use App\Models\Circuit;
use App\Models\LocalChurch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_download_the_csv_template_without_signing_in(): void
    {
        $response = $this->get('/template.csv');

        $response->assertOk()
            ->assertDownload('ayp-registration-template.csv');

        $this->assertSame(
            'First Name,Middle Name,Last Name,Church Name,Circuit Name,Birthday,Baptismal,Type',
            trim($response->streamedContent()),
        );
    }

    public function test_anyone_can_download_the_list_of_valid_circuit_and_church_names(): void
    {
        $digos = Circuit::factory()->create(['name' => 'Digos City']);
        LocalChurch::factory()->create(['circuit_id' => $digos->id, 'name' => 'Grace Church']);
        LocalChurch::factory()->create(['circuit_id' => $digos->id, 'name' => 'Closed Church'])->delete();

        $response = $this->get('/churches.csv');

        $response->assertOk()->assertDownload('ayp-valid-churches.csv');
        $rows = array_map(
            str_getcsv(...),
            explode("\n", trim(ltrim($response->streamedContent(), "\xEF\xBB\xBF"))),
        );

        $this->assertSame(
            [['Circuit Name', 'Church Name'], ['Digos City', 'Grace Church']],
            $rows,
        );
    }
}
