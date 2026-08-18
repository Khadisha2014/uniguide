<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

// A Vercel function can only write to /tmp. Create an isolated database for
// each cold instance and keep runtime sessions out of SQLite.
$databasePath = '/tmp/database.sqlite';
$initializeDatabase = ! file_exists($databasePath);

if ($initializeDatabase) {
    touch($databasePath);
}

foreach ([
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $databasePath,
    'SESSION_DRIVER' => 'file',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'LOG_CHANNEL' => 'stderr',
] as $name => $value) {
    putenv("{$name}={$value}");
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

if ($initializeDatabase) {
    $app->make(Kernel::class)->bootstrap();
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--force' => true]);
}

// Vercel functions may only write temporary files to /tmp.
$storagePath = '/tmp/storage';

foreach ([
    $storagePath.'/app/private',
    $storagePath.'/app/public',
    $storagePath.'/framework/cache/data',
    $storagePath.'/framework/sessions',
    $storagePath.'/framework/views',
    $storagePath.'/logs',
] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

$app->useStoragePath($storagePath);
$app->handleRequest(Request::capture());
