<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check slots in database with all fields
$slots = DB::table('college_timetable_slots')
    ->where('timetable_id', 1)
    ->select('id', 'day_of_week', 'start_time', 'end_time', 'is_active', 'deleted_at')
    ->get();

echo "Slots in database:\n";
foreach($slots as $slot) {
    echo "ID: {$slot->id} | Day: {$slot->day_of_week} | Time: {$slot->start_time} - {$slot->end_time} | Active: " . ($slot->is_active ? 'Yes' : 'No') . " | Deleted: " . ($slot->deleted_at ?? 'No') . "\n";
}
