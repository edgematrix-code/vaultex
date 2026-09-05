<?php

/*
|--------------------------------------------------------------------------
| Environment Loading (Overwrite Mode)
|--------------------------------------------------------------------------
|
| Laravel's default LoadEnvironmentVariables bootstrapper uses Dotenv::safeLoad(),
| which silently skips variables that are already set in the environment.
| When the environment (shell, Herd, deploy container) has stale DB_* values
| pre-set, those override the .env file — causing "Unknown database 'vaultis'"
| even though .env says DB_NAME=vaultex.
|
| This file loads the .env with overwrite mode BEFORE the Application is
| created, so the .env values always take precedence.
|
*/

if (! defined('LARAVEL_START')) {
    /**
     * Define LARAVEL_START early so downstream code can use it.
     */
    define('LARAVEL_START', microtime(true));

    /*
     * Register the Composer autoloader if not already registered.
     */
    if (! class_exists('Composer\Autoload\ClassLoader') && file_exists($autoload = dirname(__DIR__).'/vendor/autoload.php')) {
        require $autoload;
    }

    /*
     * Load the .env file with overwrite mode BEFORE Laravel's bootstrapper runs.
     *
     * Laravel's LoadEnvironmentVariables uses Dotenv::safeLoad(), which silently
     * skips variables already set in the environment. If the shell / Herd / deploy
     * container has stale DB_* values pre-set, those would override .env and cause
     * errors like "Unknown database 'vaultis'" even though .env says DB_NAME=vaultex.
     *
     * This file uses load() (overwrite mode) so .env values always win.
     */
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env');
        $dotenv->load();
    }
}
