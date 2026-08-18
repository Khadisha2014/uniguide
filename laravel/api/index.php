<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\URL;
use Illuminate\View\ViewServiceProvider;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

// A Vercel function can only write to /tmp. Create an isolated database for
// each cold instance and keep runtime sessions out of SQLite.
$temporaryPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
$databasePath = $temporaryPath.DIRECTORY_SEPARATOR.'uniguide-database.sqlite';

if (! file_exists($databasePath)) {
    $database = new PDO('sqlite:'.$databasePath);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $database->exec('CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR NOT NULL,
        email VARCHAR NOT NULL UNIQUE,
        email_verified_at DATETIME NULL,
        password VARCHAR NOT NULL,
        remember_token VARCHAR NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )');
    $database->exec('CREATE TABLE universities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR NOT NULL UNIQUE,
        short_name VARCHAR(20) NOT NULL,
        city VARCHAR NOT NULL,
        country VARCHAR NOT NULL,
        flag VARCHAR(10) NULL,
        world_rank INTEGER NOT NULL,
        acceptance_rate INTEGER NOT NULL,
        international_rate INTEGER NOT NULL,
        tuition VARCHAR NOT NULL,
        tuition_value INTEGER NOT NULL DEFAULT 0,
        requirements TEXT NOT NULL,
        deadline VARCHAR NOT NULL,
        type VARCHAR NOT NULL,
        accent VARCHAR(7) NOT NULL DEFAULT "#0d6b52",
        description TEXT NULL,
        is_published INTEGER NOT NULL DEFAULT 1,
        created_at DATETIME NULL,
        updated_at DATETIME NULL
    )');

    $now = gmdate('Y-m-d H:i:s');
    $user = $database->prepare('INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
    $user->execute(['Администратор', 'admin@uniguide.local', password_hash('UniGuide2026!', PASSWORD_BCRYPT), $now, $now]);

    $university = $database->prepare('INSERT INTO universities (
        name, short_name, city, country, flag, world_rank, acceptance_rate,
        international_rate, tuition, tuition_value, requirements, deadline,
        type, accent, is_published, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)');

    foreach (require __DIR__.'/../database/universities.php' as $item) {
        $university->execute([
            $item[0], $item[1], $item[2], $item[3], $item[4], $item[5],
            $item[6], $item[7], $item[8], $item[9],
            json_encode($item[10], JSON_UNESCAPED_UNICODE), $item[11],
            $item[12], $item[13], $now, $now,
        ]);
    }
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

// Vercel functions may only write temporary files to /tmp.
$storagePath = $temporaryPath.DIRECTORY_SEPARATOR.'uniguide-storage';
$bootstrapPath = $temporaryPath.DIRECTORY_SEPARATOR.'uniguide-bootstrap';

foreach ([
    $bootstrapPath.'/cache',
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

/** @var Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
$app->useBootstrapPath($bootstrapPath);
$app->useStoragePath($storagePath);

try {
    $app->make(Kernel::class)->bootstrap();
} catch (Throwable $exception) {
    error_log('UNIGUIDE_BOOTSTRAP_FAILURE '.get_class($exception).': '.$exception->getMessage());
    http_response_code(500);
    echo 'Laravel bootstrap failed. Check the Vercel runtime log.';
    exit;
}

if (! $app->bound('view')) {
    $app->register(ViewServiceProvider::class);
}

URL::forceScheme('https');

$app->handleRequest(Request::capture());
