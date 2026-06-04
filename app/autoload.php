<?php

use Composer\Autoload\ClassLoader;
use Symfony\Component\Dotenv\Dotenv;

/** @var ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

$projectDir = dirname(__DIR__);
if (class_exists(Dotenv::class) && (file_exists($projectDir.'/.env') || file_exists($projectDir.'/.env.local'))) {
    (new Dotenv())->loadEnv($projectDir.'/.env');
}

return $loader;
