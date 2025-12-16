<?php
// Application Configuration
$_ENV['REL_BASE_URL'] = '/webshop';

//initiate session variables

//Keep track of visited pages in session (avoid increment view with refresh/ spam)
if (!isset($_SESSION['visited'])) {
    $_SESSION['visited'] = [];
}



$envFile = dirname(__DIR__, 2) . '/.env';
//extract the .env vars
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}



// Timezone
date_default_timezone_set('Europe/Amsterdam');
