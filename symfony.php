<?php
require __DIR__ . '/psr-11-v1/vendor/autoload.php';
require __DIR__ . '/setup.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;

$container = new ContainerBuilder();

$container->register(PDO::class,PDO::class)
    ->setArguments([
      '%env(DB_DSN)%',
      '%env(DB_USERNAME)%',
      '%env(DB_PASSWORD)%',
    ]);

$container->autowire(Foo::class,Foo::class)
    ->setPublic(true)
    ->setArgument('$bar', 'bar-wrong')
    ->setArgument('$baz', 'baz-right');

$container->getDefinition(Foo::class)
    ->setArgument('$bar', 'bar-right');

putenv('DB_DSN=sqlite::memory:');
putenv('DB_USERNAME=db_user');
putenv('DB_PASSWORD=db_password');

$container->compile(true);

echo "Symfony" . PHP_EOL;
output($container, 'get');
