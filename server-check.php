<?php

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Robots-Tag: noindex, nofollow');

$basePath = __DIR__;
$environmentPath = $basePath.'/.env';
$hasApplicationKey = false;

if (is_file($environmentPath)) {
    foreach (file($environmentPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (str_starts_with($line, 'APP_KEY=')) {
            $hasApplicationKey = trim(substr($line, strlen('APP_KEY=')), " \t\n\r\0\x0B\"'") !== '';
            break;
        }
    }
}

$checks = [
    'root_index' => is_file($basePath.'/index.php'),
    'root_htaccess' => is_file($basePath.'/.htaccess'),
    'public_index' => is_file($basePath.'/public/index.php'),
    'public_htaccess' => is_file($basePath.'/public/.htaccess'),
    'composer_json' => is_file($basePath.'/composer.json'),
    'vendor_autoload' => is_file($basePath.'/vendor/autoload.php'),
    'env_file' => is_file($environmentPath),
    'app_key_present' => $hasApplicationKey,
    'php_83_or_newer' => PHP_VERSION_ID >= 80300,
];

echo "Loan Suite server check: OK\n";
echo 'Checked at: '.gmdate('c')."\n";
echo 'PHP version: '.PHP_VERSION."\n";
echo 'Request URI: '.($_SERVER['REQUEST_URI'] ?? 'unknown')."\n\n";

echo "Required file checks:\n";
foreach ($checks as $name => $passed) {
    echo '- '.$name.': '.($passed ? 'yes' : 'no')."\n";
}

echo "\nIf this file is reachable but Laravel is not, fix any check marked no. If this file is not reachable, cPanel is serving a different folder than the Git deployment folder.\n";
