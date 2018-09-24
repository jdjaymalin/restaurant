<?php

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv(__DIR__.'/../');
$dotenv->load();

$defaultDB = env('DB_Default', 'prodDatabase');
$dbConfig = require __DIR__ . '/../database/database.php';

$app = new \Illuminate\Container\Container();
$app->singleton('app', \Illuminate\Container\Container::class);

$db = new Capsule($app);
foreach ($dbConfig as $dbName => $dbAttributes) {
    $db->addConnection($dbAttributes, $dbName);
}

$db->getDatabaseManager()->setDefaultConnection($defaultDB);
$db->setAsGlobal();
$db->bootEloquent();
