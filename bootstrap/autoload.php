<?php

if(! defined('LARAVEL_START'))
{
    define('LARAVEL_START', microtime(true));
}

/**
 * load the waypoint helper functions. Note that these functions override the functions
 * defined by Laravel's vendor/laravel/framework/src/Illuminate/Support/helpers.php
 */
require __DIR__ . '/helper.php';
/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
| if PHPUNIT_COMPOSER_INSTALL is defined, that means that phpunit has found an autoloader to
| load. See line 30 of the phpunit script - probably located at vendor/phpunit/phpunit/phpunit
*/
if (!defined('PHPUNIT_COMPOSER_INSTALL'))
{
    require __DIR__ . '/../vendor/autoload.php';
}
/*
|--------------------------------------------------------------------------
| Include The Compiled Class File
|--------------------------------------------------------------------------
|
| To dramatically increase your application's performance, you may use a
| compiled class file which contains all of the classes commonly used
| by a request. The Artisan "optimize" is used to create this file.
|
*/

$compiledPath = __DIR__.'/cache/compiled.php';

if (file_exists($compiledPath)) {
    /** @noinspection PhpIncludeInspection */
    require $compiledPath;
}
