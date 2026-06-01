<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$autoloadPath = __DIR__.'/../vendor/autoload.php';
$appPath = __DIR__.'/../bootstrap/app.php';
$environmentPath = __DIR__.'/../.env';
$environmentApplicationKey = getenv('APP_KEY');
$hasApplicationKey = is_string($environmentApplicationKey) && trim($environmentApplicationKey) !== '';

if (! $hasApplicationKey && file_exists($environmentPath)) {
    foreach (file($environmentPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (strpos($line, 'APP_KEY=') === 0) {
            $hasApplicationKey = trim(substr($line, strlen('APP_KEY=')), " \t\n\r\0\x0B\"'") !== '';
            break;
        }
    }
}

if (! file_exists($autoloadPath) || ! file_exists($appPath) || ! $hasApplicationKey || PHP_VERSION_ID < 80300) {
    http_response_code(503);
    require __DIR__.'/../resources/views/deployment-fallback.php';
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $autoloadPath;

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appPath;

$app->handleRequest(Request::capture());
