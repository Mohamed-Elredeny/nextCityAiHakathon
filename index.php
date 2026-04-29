<?php

/*
 |--------------------------------------------------------------------------
 | Project-root entry point — for sub-URI deployments like /hackathon/
 |--------------------------------------------------------------------------
 | This file makes the project root the public-facing entry point so the
 | URL `https://aiu.edu.eg/hackathon/login` works without `/public/` in it.
 |
 | Paths point to ./vendor and ./bootstrap (no `/../`) because this file
 | lives at the project root, unlike public/index.php which is one level in.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoloader
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel + handle the request
(require_once __DIR__.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
