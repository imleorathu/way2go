<?php
declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

function detect_base_url(): string
{
    $configured = env_value('APP_BASE_URL');
    if ($configured !== null) {
        return rtrim($configured, '/');
    }

    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $segments = array_values(array_filter(explode('/', trim($scriptName, '/'))));
    if (!$segments) {
        return '';
    }

    $rootScripts = ['index.php', 'setup.php'];
    $rootFolders = ['admin', 'app', 'auth', 'customer', 'database', 'public', 'storage'];
    if (in_array($segments[0], $rootScripts, true) || in_array($segments[0], $rootFolders, true)) {
        return '';
    }

    return '/' . $segments[0];
}

define('APP_NAME', env_value('APP_NAME', 'Way2Go'));
define('BASE_URL', detect_base_url());

define('DB_HOST', env_value('DB_HOST', '127.0.0.1'));
define('DB_NAME', env_value('DB_NAME', 'way2go'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));

date_default_timezone_set('Asia/Colombo');

if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = dirname(__DIR__) . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }
    session_save_path($sessionPath);
    session_start();
}
