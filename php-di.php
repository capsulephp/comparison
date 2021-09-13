<?php
require __DIR__ . '/psr-11-v1/vendor/autoload.php';
require __DIR__ . '/setup.php';

use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAnnotations(false);

$containerBuilder->addDefinitions([
    PDO::CLASS => DI\autowire()
        ->constructor(
            DI\env('DB_DSN'),
            DI\env('DB_USERNAME'),
            DI\env('DB_PASSWORD')
        ),
    Foo::CLASS => DI\autowire()
        // use a configuration value
        ->constructorParameter('bar', \DI\get('Foo:bar'))
        ->constructorParameter('baz', 'baz-right'),
    'Foo:bar' => 'bar-wrong',
]);

// simulate override via configuration values
$containerBuilder->addDefinitions([
    'Foo:bar' => 'bar-right',
]);

$container = $containerBuilder->build();
echo "PHP-DI" . PHP_EOL;
output($container, 'get');
