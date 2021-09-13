<?php
require __DIR__ . '/psr-11-v1/vendor/autoload.php';
require __DIR__ . '/setup.php';

use Illuminate\Container\Container;

$container = new Container();

$container->when(PDO::CLASS)
    ->needs('$dsn')
    ->giveConfig('PDO:dsn');

$container->when(PDO::CLASS)
    ->needs('$username')
    ->giveConfig('PDO:username');

$container->when(PDO::CLASS)
    ->needs('$password')
    ->giveConfig('PDO:password');

$container->when(Foo::CLASS)
    ->needs('$bar')
    ->give('bar-wrong');

$container->when(Foo::CLASS)
    ->needs('$baz')
    ->give('baz-right');

// simulate override
$container->when(Foo::CLASS)
    ->needs('$bar')
    ->give('bar-right');

// lazy-load environment values ...
$configFactory = function () {
    $config = new Container();
    $config['PDO:dsn'] = getenv('DB_DSN');
    $config['PDO:username'] = getenv('DB_USERNAME');
    $config['PDO:password'] = getenv('DB_PASSWORD');
    return $config;
};

// and bind them where the container expects them
$container->bind('config', $configFactory, true);

// done
echo "Illuminate (Laravel)" . PHP_EOL;
output($container, 'get');
