<?php
$schools = DB::table('schools')
    ->whereIn('sppg_id', [9, 11])
    ->get();
echo "SCHOOLS FOR SPPG 9 and 11:" . PHP_EOL;
foreach ($schools as $s) {
    echo "ID: {$s->id} | SPPG: {$s->sppg_id} | Name: {$s->name} | Lat: {$s->latitude} | Lng: {$s->longitude} | Addr: {$s->address}" . PHP_EOL;
}
