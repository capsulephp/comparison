<?php
class Foo
{
    public $pdo;

    public $bar;

    public $baz;

    public function __construct(
        PDO $pdo,
        string $bar,
        string $baz = 'baz-wrong'
    ) {
        $this->pdo = $pdo;
        $this->bar = $bar;
        $this->baz = $baz;
    }
}

function output($container, $method)
{
    putenv('DB_DSN=sqlite::memory:');
    putenv('DB_USERNAME=dbuser');
    putenv('DB_PASSWORD=dbpass');
    $foo = $container->$method(Foo::CLASS);
    echo get_class($foo->pdo) . PHP_EOL;
    echo $foo->bar . PHP_EOL;
    echo $foo->baz . PHP_EOL;
    echo PHP_EOL;
}
