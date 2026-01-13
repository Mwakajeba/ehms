<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check slots in database
$slots = DB::table('college_timetable_slots')->where('timetable_id', 1)->get();
echo "Total slots: " . count($slots) . "\n";
foreach($slots as $slot) {
    echo "ID: " . $slot->id . " | Day: " . $slot->day_of_week . " | Time: " . $slot->start_time . " - " . $slot->end_time . " | Course ID: " . $slot->course_id . "\n";
}

// Check slotsByDay from model
$timetable = App\Models\College\Timetable::find(1);
if ($timetable) {
    echo "\n--- Slots from Model ---\n";
    echo "Timetable name: " . $timetable->name . "\n";
    echo "Slots count: " . $timetable->slots->count() . "\n";
    
    $slotsByDay = $timetable->getSlotsByDay();
    echo "\n--- Slots by Day ---\n";
    print_r(array_keys($slotsByDay));
    foreach ($slotsByDay as $day => $daySlots) {
        echo "\n$day: " . count($daySlots) . " slots\n";
        foreach ($daySlots as $s) {
            echo "  - " . $s->start_time . " - " . $s->end_time . "\n";
        }
    }
}
