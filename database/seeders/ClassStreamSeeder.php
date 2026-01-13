<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School\Classe;
use App\Models\School\Stream;

class ClassStreamSeeder extends Seeder
{
    public function run()
    {
        // Create some default streams if they don't exist
        $streams = [
            ['name' => 'RED', 'description' => 'Red Stream'],
            ['name' => 'BLUE', 'description' => 'Blue Stream'],
            ['name' => 'GREEN', 'description' => 'Green Stream'],
            ['name' => 'YELLOW', 'description' => 'Yellow Stream'],
        ];

        $company = \App\Models\Company::first();
        $branch = \App\Models\Branch::first();

        if ($company && $branch) {
            foreach ($streams as $streamData) {
                $stream = Stream::firstOrCreate(
                    ['name' => $streamData['name']],
                    [
                        'description' => $streamData['description'],
                        'company_id' => $company->id,
                        'branch_id' => $branch->id,
                    ]
                );
            }

            // Get all classes and streams
            $classes = Classe::where('company_id', $company->id)->get();
            $allStreams = Stream::where('company_id', $company->id)->get();

            // Assign all streams to all classes (many-to-many relationship)
            foreach ($classes as $class) {
                foreach ($allStreams as $stream) {
                    $class->streams()->syncWithoutDetaching([$stream->id]);
                }
            }
        }
    }
}