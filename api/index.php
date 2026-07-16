<?php
// Force base URL to be empty on Vercel by overriding SCRIPT_NAME and SCRIPT_FILENAME
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = '/index.php';

// Forward Vercel requests to Laravel's public entrypoint
require __DIR__ . '/../public/index.php';

