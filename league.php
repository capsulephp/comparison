<?php
require __DIR__ . '/psr-11-v2/vendor/autoload.php';
require __DIR__ . '/setup.php';

use League\Container\Argument\ResolvableArgument;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;
use League\Container\ReflectionContainer;

$container = new Container();
$container->delegate(new ReflectionContainer());

$container->add(PDO::CLASS)
    ->addArguments([
        new ResolvableArgument('PDO:dsn'),
        new ResolvableArgument('PDO:username'),
        new ResolvableArgument('PDO:password'),
    ]);

$container->add(Foo::CLASS)
    ->addArguments([
        new ResolvableArgument(PDO::CLASS),
        new ResolvableArgument('Foo:bar'),
        new StringArgument('baz-right'),
    ]);

$container->add('Foo:bar', 'bar-wrong');

$container->add('PDO:dsn', function () { return getenv('DB_DSN'); });
$container->add('PDO:username', function () { return getenv('DB_USERNAME'); });
$container->add('PDO:password', function () { return getenv('DB_PASSWORD'); });

// simulate override
$container->add('Foo:bar', 'bar-right');

echo "League" . PHP_EOL;
output($container, 'get');
