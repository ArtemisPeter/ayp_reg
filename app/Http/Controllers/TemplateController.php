<?php

namespace App\Http\Controllers;

use App\Models\LocalChurch;
use App\Registration\RosterImport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateController extends Controller
{
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo implode(',', RosterImport::COLUMNS)."\r\n";
        }, 'ayp-registration-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function churches(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Circuit Name', 'Church Name']);

            LocalChurch::with('circuit')->get()
                ->sortBy(fn (LocalChurch $church) => [$church->circuit->name, $church->name])
                ->each(fn (LocalChurch $church) => fputcsv($out, [$church->circuit->name, $church->name]));

            fclose($out);
        }, 'ayp-valid-churches.csv', ['Content-Type' => 'text/csv']);
    }
}
