<?php

echo "=== EMPLOYEES FOR SPPG 9 (Coblong) & SPPG 11 (Lengkong) ===" . PHP_EOL;
$employees = DB::table('employees')
    ->whereIn('sppg_id', [9, 11])
    ->get();
foreach ($employees as $emp) {
    echo "ID: {$emp->id}, Name: {$emp->name}, SPPG: {$emp->sppg_id}, Role: {$emp->role_type} (user_id: {$emp->user_id})" . PHP_EOL;
}

echo PHP_EOL . "=== SCHOOLS/PARTNERS FOR SPPG 9 (Coblong) & SPPG 11 (Lengkong) ===" . PHP_EOL;
$partners = DB::table('partners')
    ->whereIn('sppg_id', [9, 11])
    ->get();
foreach ($partners as $p) {
    echo "ID: {$p->id}, Name: {$p->school_name}, SPPG: {$p->sppg_id}, NPSN: {$p->npsn}" . PHP_EOL;
}

echo PHP_EOL . "=== ADMIN USERS FOR SPPG 9 & SPPG 11 ===" . PHP_EOL;
$users = DB::table('users')
    ->whereIn('id', [18, 20])
    ->get();
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Email: {$u->email}, Role: {$u->role_name}" . PHP_EOL;
}
