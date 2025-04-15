<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Use absolute path for console
$consolePath = dirname(__DIR__).'/bin/console';

if ($_SERVER['APP_ENV'] === 'test') {
    passthru(sprintf(
        'APP_ENV=test php %s doctrine:database:drop --force --if-exists',
        $consolePath
    ));
    passthru(sprintf(
        'APP_ENV=test php %s doctrine:database:create',
        $consolePath
    ));
    passthru(sprintf(
        'APP_ENV=test php %s doctrine:migrations:migrate -n --allow-no-migration',
        $consolePath
    ));
}
