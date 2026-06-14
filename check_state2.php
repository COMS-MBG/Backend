<?php

echo "=== Partners (using school_name) ===" . PHP_EOL;
$partners = DB::table('partners')->select('id','sppg_id','school_name','npsn','school_type','portion_count')->get();
echo "Total partners: " . count($partners) . PHP_EOL;
foreach ($partners as $p) {
    echo "Partner ID: {$p->id} | SPPG: {$p->sppg_id} | {$p->school_name} | NPSN: {$p->npsn}" . PHP_EOL;
}

echo PHP_EOL . "=== All Employees (with position info) ===" . PHP_EOL;
$allEmps = DB::table('employees')
    ->leftJoin('users', 'employees.user_id', '=', 'users.id')
    ->select('employees.id','employees.sppg_id','employees.name','employees.position','users.id as uid')
    ->orderBy('employees.sppg_id')
    ->get();
echo "Total employees: " . count($allEmps) . PHP_EOL;
foreach ($allEmps as $e) {
    echo "Emp ID: {$e->id} | SPPG: {$e->sppg_id} | {$e->name} | pos: {$e->position} | user_id: {$e->uid}" . PHP_EOL;
}

echo PHP_EOL . "=== Delivery Schedules columns ===" . PHP_EOL;
$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'delivery_schedules' ORDER BY ordinal_position");
foreach ($cols as $c) {
    echo $c->column_name . PHP_EOL;
}

echo PHP_EOL . "=== Delivery Histories columns ===" . PHP_EOL;
$hcols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'delivery_histories' ORDER BY ordinal_position");
foreach ($hcols as $c) {
    echo $c->column_name . PHP_EOL;
}

echo PHP_EOL . "=== Active SPPGs (3,1,2,4,9,11) - check users ===" . PHP_EOL;
$users = DB::table('users')->select('id','name','email','sppg_id')->whereIn('sppg_id', [1,2,3,4,9,11])->get();
foreach ($users as $u) {
    echo "User ID: {$u->id} | SPPG: {$u->sppg_id} | {$u->name} | {$u->email}" . PHP_EOL;
}
