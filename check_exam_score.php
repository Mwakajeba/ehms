<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$score = App\Models\College\FinalExamScore::with(['examSchedule', 'courseRegistration.course'])->first();
if ($score) {
    echo "FinalExamScore data:\n";
    echo "  id: " . $score->id . "\n";
    echo "  score: " . $score->score . "\n";
    echo "  max_marks: " . ($score->max_marks ?? 'N/A') . "\n";
    echo "  weighted_score: " . $score->weighted_score . "\n";
    echo "  status: " . $score->status . "\n";
    
    if ($score->examSchedule) {
        echo "\nExamSchedule data:\n";
        echo "  total_marks: " . ($score->examSchedule->total_marks ?? 'N/A') . "\n";
        echo "  pass_marks: " . ($score->examSchedule->pass_marks ?? 'N/A') . "\n";
    }
    
    if ($score->courseRegistration && $score->courseRegistration->course) {
        echo "\nCourse: " . $score->courseRegistration->course->name . "\n";
    }
} else {
    echo "No score found\n";
}
