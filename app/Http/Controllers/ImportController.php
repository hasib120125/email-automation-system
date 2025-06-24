<?php

namespace App\Http\Controllers;

use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function __construct(
        private CsvImportService $csvImportService
    ) {}

    public function showImportForm()
    {
        return view('import.djs-import');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('csv_file');
            $filePath = $file->store('temp', 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            $results = $this->csvImportService->importUsers($fullPath);

            // Clean up temp file
            Storage::disk('local')->delete($filePath);

            $message = "Import completed! Created: {$results['created']}, Updated: {$results['updated']}, Total: {$results['total']}";

            if (!empty($results['errors'])) {
                $message .= "\n\nErrors:\n" . implode("\n", $results['errors']);
                return redirect()->back()->with('warning', $message);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}