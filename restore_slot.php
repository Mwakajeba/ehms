<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Restore deleted slot
DB::table('college_timetable_slots')->where('id', 1)->update(['deleted_at' => null]);
echo "Slot 1 restored!\n";

// Verify
$slots = DB::table('college_timetable_slots')->where('timetable_id', 1)->get();
echo "Total active slots now: " . count($slots) . "\n";
foreach($slots as $slot) {
    echo "ID: {$slot->id} | Day: {$slot->day_of_week} | Time: {$slot->start_time} - {$slot->end_time}\n";
}
