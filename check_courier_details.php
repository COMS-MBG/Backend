<?php
$user = DB::table('users')->where('id', 26)->first();
echo "USER 26:" . PHP_EOL;
print_r($user);

$emp = DB::table('employees')->where('id', 25)->first();
echo PHP_EOL . "EMPLOYEE 25:" . PHP_EOL;
print_r($emp);

$roles = DB::table('model_has_roles')->where('model_id', 26)->get();
echo PHP_EOL . "ROLES FOR USER 26:" . PHP_EOL;
print_r($roles);
