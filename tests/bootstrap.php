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

    // Get doctrine and fixture loader
    $entityManager = $container->get('doctrine.orm.entity_manager');
    $fixtureLoader = $container->get('doctrine.fixtures.loader');

    // Load fixtures
    $fixtures = $fixtureLoader->getFixtures();
    $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($entityManager, new \Doctrine\Common\DataFixtures\Purger\ORMPurger());
    $executor->execute($fixtures);
}
