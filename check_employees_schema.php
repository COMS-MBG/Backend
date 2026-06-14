<?php
$col = DB::getSchemaBuilder()->getColumnListing('employees');
echo "Columns of employees:" . PHP_EOL;
print_r($col);
