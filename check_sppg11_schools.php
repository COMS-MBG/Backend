<?php
$count = DB::table('schools')->where('sppg_id', 11)->count();
echo "Total schools for SPPG 11: {$count}" . PHP_EOL;

// Check all schools in schools table and print their sppg_id
$sppgs = DB::table('schools')->select('sppg_id', DB::raw('count(*) as count'))->groupBy('sppg_id')->get();
foreach ($sppgs as $s) {
    echo "SPPG ID: " . ($s->sppg_id ?? 'NULL') . " | count: {$s->count}" . PHP_EOL;
}
