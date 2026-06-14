<?php
$roles = DB::table('roles')->get();
foreach ($roles as $r) {
    echo "ID: {$r->id}, Name: {$r->name}, Display: {$r->display_name}, Guard: {$r->guard_name}" . PHP_EOL;
}
