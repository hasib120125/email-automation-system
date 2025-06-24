<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTags = [
            [
                'name' => 'Afro-Caribbean',
                'slug' => 'afro-caribbean',
                'color' => '#10B981',
                'description' => 'DJs specializing in Afro-Caribbean music genres'
            ],
            [
                'name' => 'Generalist', 
                'slug' => 'generalist',
                'color' => '#3B82F6',
                'description' => 'DJs who play various music genres'
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'color' => '#F59E0B',
                'description' => 'Premium tier DJs with higher rates'
            ],
            [
                'name' => 'Club Only',
                'slug' => 'club-only',
                'color' => '#8B5CF6',
                'description' => 'DJs who only perform at club venues'
            ],
        ];

        foreach ($defaultTags as $tagData) {
            Tag::firstOrCreate(
                ['slug' => $tagData['slug']],
                $tagData
            );
        }
    }
}
