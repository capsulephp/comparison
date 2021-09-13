<?php
require __DIR__ . '/psr-11-v1/vendor/autoload.php';
require __DIR__ . '/setup.php';

$injector = new Auryn\Injector();

$injector->delegate(PDO::CLASS, function () {
    return new PDO(
        getenv('DB_DSN'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
});

$injector->define(Foo::CLASS, [
    ':bar' => 'bar-wrong',
    ':baz' => 'baz-right',
]);

$injector->define(Foo::CLASS, [
    ':bar' => 'bar-right',
]);

echo "Auryn" . PHP_EOL;
output($injector, 'make');
