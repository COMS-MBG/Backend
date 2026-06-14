<?php
$employees = DB::table('employees')
    ->where('sppg_id', 11)
    ->get();
echo "EMPLOYEES FOR SPPG 11:" . PHP_EOL;
foreach ($employees as $e) {
    echo "ID: {$e->id} | Name: {$e->name} | Position: {$e->position} | User ID: {$e->user_id}" . PHP_EOL;
}
