<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Load fixtures once for all tests
if (isset($_ENV['BOOTSTRAP_LOAD_FIXTURES']) && $_ENV['BOOTSTRAP_LOAD_FIXTURES']) {
    // Create kernel
    require_once dirname(__DIR__).'/src/Kernel.php';
    $kernel = new App\Kernel('test', false);
    $kernel->boot();
    $container = $kernel->getContainer();

    // Load fixtures using console command
    passthru(sprintf(
        'APP_ENV=%s php "%s/../bin/console" doctrine:fixtures:load --no-interaction',
        $_ENV['APP_ENV'],
        __DIR__
    ));
}
