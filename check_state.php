<?php

// Check SPPGs - we know these
echo "=== SPPGs ===" . PHP_EOL;
$sppgs = DB::table('s_p_p_g_s')->select('id','name','status')->get();
foreach ($sppgs as $s) {
    echo "SPPG ID: {$s->id} | {$s->name} | status: {$s->status}" . PHP_EOL;
}

echo PHP_EOL . "=== Schools columns ===" . PHP_EOL;
$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'schools' ORDER BY ordinal_position");
foreach ($cols as $c) {
    echo $c->column_name . PHP_EOL;
}

echo PHP_EOL . "=== Schools ===" . PHP_EOL;
$schools = DB::table('schools')->select('id','sppg_id','name','status')->orderBy('sppg_id')->get();
echo "Total schools: " . count($schools) . PHP_EOL;
foreach ($schools as $s) {
    echo "School ID: {$s->id} | SPPG: {$s->sppg_id} | {$s->name}" . PHP_EOL;
}

echo PHP_EOL . "=== Partners columns ===" . PHP_EOL;
$pcols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'partners' ORDER BY ordinal_position");
foreach ($pcols as $c) {
    echo $c->column_name . PHP_EOL;
}

echo PHP_EOL . "=== Partners ===" . PHP_EOL;
$partners = DB::table('partners')->select('id','sppg_id','name','npsn','school_type')->get();
echo "Total partners: " . count($partners) . PHP_EOL;
foreach ($partners as $p) {
    echo "Partner ID: {$p->id} | SPPG: {$p->sppg_id} | {$p->name} | NPSN: {$p->npsn}" . PHP_EOL;
}

echo PHP_EOL . "=== Employees (Couriers) ===" . PHP_EOL;
$employees = DB::table('employees')
    ->join('users', 'employees.user_id', '=', 'users.id')
    ->select('employees.id','employees.sppg_id','employees.name','employees.position','employees.status','users.id as uid','users.email')
    ->whereIn('employees.position', ['courier', 'Courier', 'COURIER'])
    ->get();
echo "Total couriers by position: " . count($employees) . PHP_EOL;
foreach ($employees as $e) {
    echo "Emp ID: {$e->id} | SPPG: {$e->sppg_id} | {$e->name} | user_id: {$e->uid} | {$e->email}" . PHP_EOL;
}

echo PHP_EOL . "=== All Employees (sample) ===" . PHP_EOL;
$allEmps = DB::table('employees')
    ->join('users', 'employees.user_id', '=', 'users.id')
    ->select('employees.id','employees.sppg_id','employees.name','employees.position','users.id as uid')
    ->limit(20)
    ->get();
foreach ($allEmps as $e) {
    echo "Emp ID: {$e->id} | SPPG: {$e->sppg_id} | {$e->name} | pos: {$e->position} | user_id: {$e->uid}" . PHP_EOL;
}

echo PHP_EOL . "=== Delivery Schedules ===" . PHP_EOL;
$ds = DB::table('delivery_schedules')->count();
echo "Total: {$ds}" . PHP_EOL;

echo PHP_EOL . "=== Delivery Histories ===" . PHP_EOL;
$dh = DB::table('delivery_histories')->count();
echo "Total: {$dh}" . PHP_EOL;
