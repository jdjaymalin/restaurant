<?php

use Database\Migration;

require __DIR__ . '/../bootstrap/autoload.php';

echo "Creating tables\n";

$migrations = new Migration();
$migrations::up();

if ($argv[1] === 'seed') {
    echo "Seeding the database\n";
    $migrations::seed();
}
