<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CsvImportService
{
    public function importUsers(string $filePath): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => [],
            'total' => 0
        ];

        if (!file_exists($filePath)) {
            $results['errors'][] = 'File not found';
            return $results;
        }

        $csvData = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csvData);

        // Normalize headers
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);

        $emailIndex = array_search('email', $headers);
        $nameIndex = array_search('name', $headers);

        if ($emailIndex === false || $nameIndex === false) {
            $results['errors'][] = 'CSV must contain email and name columns';
            return $results;
        }

        // Create or get the import_2025 tag
        $importTag = Tag::firstOrCreate([
            'name' => 'import_2025',
        ]);

        DB::transaction(function() use ($csvData, $emailIndex, $nameIndex, $importTag, &$results) {
            foreach ($csvData as $rowIndex => $row) {
                $results['total']++;

                try {
                    $email = trim($row[$emailIndex] ?? '');
                    $name = trim($row[$nameIndex] ?? '');

                    if (empty($email) || empty($name)) {
                        $results['errors'][] = "Row " . ($rowIndex + 2) . ": Missing email or name";
                        continue;
                    }

                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $results['errors'][] = "Row " . ($rowIndex + 2) . ": Invalid email format";
                        continue;
                    }

                    $user = User::where('email', $email)->first();

                    if ($user) {
                        // Update existing user
                        $user->update(['name' => $name]);
                        $results['updated']++;
                    } else {
                        // Create new user
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => Hash::make(Str::random(12)),
                            'email_verified_at' => now(),
                        ]);
                        $results['created']++;
                    }

                    // Add import tag using polymorphic relationship
                    if (!$user->tags()->where('tag_id', $importTag->id)->exists()) {
                        $user->tags()->attach($importTag);
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }
        });

        return $results;
    }
}