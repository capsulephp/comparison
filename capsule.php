<?php
require __DIR__ . '/psr-11-v2/vendor/autoload.php';
require __DIR__ . '/setup.php';

use Capsule\Di\Container;
use Capsule\Di\Definitions;

$def = new Definitions();

$def->{PDO::CLASS}
    ->arguments([
        $def->env('DB_DSN'),
        $def->env('DB_USERNAME'),
        $def->env('DB_PASSWORD')
    ]);

$def->{Foo::CLASS}
    ->argument('bar', 'bar-wrong')
    ->argument('baz', 'baz-right');

$def->{Foo::CLASS}
    ->argument('bar', 'bar-right');

$container = new Capsule\Di\Container($def);

echo "Capsule" . PHP_EOL;
output($container, 'get');
