<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School\Student;
use App\Models\School\Stream;
use App\Models\School\Classe;
use App\Models\School\AcademicYear;
use App\Models\School\BusStop;

class TestStudentSeeder extends Seeder
{
    public function run()
    {
        // Get or create required dependencies
        $class = Classe::first();
        $academicYear = AcademicYear::first();
        $busStop = BusStop::first();
        
        // Get or create a stream
        $stream = Stream::first();
        if (!$stream) {
            $stream = Stream::create([
                'name' => 'Stream A',
                'description' => 'Default test stream',
            ]);
        }
        
        // Only create student if we have at least a class
        if ($class) {
            Student::create([
                'admission_number' => 'TST001',
                'first_name' => 'Test',
                'last_name' => 'Student',
                'date_of_birth' => '2010-01-01',
                'gender' => 'male',
                'email' => 'test@student.com',
                'address' => '123 Test Street',
                'class_id' => $class->id,
                'stream_id' => $stream ? $stream->id : null,
                'academic_year_id' => $academicYear ? $academicYear->id : null,
                'boarding_type' => 'day',
                'bus_stop_id' => $busStop ? $busStop->id : null,
            ]);
        }
    }
}
