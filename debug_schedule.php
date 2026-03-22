<?php

use App\Models\Schedule;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$schedule = Schedule::first();
echo "Type of Schedule::first(): " . get_class($schedule) . "\n";
if ($schedule instanceof Schedule) {
    echo "It IS an instance of App\Models\Schedule\n";
} else {
    echo "It IS NOT an instance of App\Models\Schedule\n";
}

$query = Schedule::with('schoolClass');
$schedules = $query->get();
echo "Type of first element in collection: " . get_class($schedules->first()) . "\n";
echo "Is it stdClass? " . ($schedules->first() instanceof stdClass ? "YES" : "NO") . "\n";
echo "Is it Schedule? " . ($schedules->first() instanceof Schedule ? "YES" : "NO") . "\n";
