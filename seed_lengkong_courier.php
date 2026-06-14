<?php

echo "=== Seeding Courier for SPPG 11 (Lengkong) ===" . PHP_EOL;

$email = 'asep.lengkong@sppg.test';
$userExists = DB::table('users')->where('email', $email)->first();

if (!$userExists) {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Asep Kurir Lengkong',
        'email' => $email,
        'password' => bcrypt('password'),
        'phone' => '081234567891',
        'is_active' => 1,
        'role_type' => 'sppg_user',
        'sppg_id' => 11,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "  + Created User: {$email} (ID: {$userId})" . PHP_EOL;
} else {
    $userId = $userExists->id;
    echo "  ~ User already exists (ID: {$userId})" . PHP_EOL;
}

$empExists = DB::table('employees')
    ->where('sppg_id', 11)
    ->where('user_id', $userId)
    ->first();

if (!$empExists) {
    $empId = DB::table('employees')->insertGetId([
        'sppg_id' => 11,
        'user_id' => $userId,
        'role_id' => 5, // Kurir role ID
        'name' => 'Asep Kurir Lengkong',
        'position' => 'courier',
        'phone' => '081234567891',
        'status' => 'active',
        'joined_at' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "  + Created Employee Courier (ID: {$empId})" . PHP_EOL;
} else {
    echo "  ~ Employee Courier already exists (ID: {$empExists->id})" . PHP_EOL;
}
