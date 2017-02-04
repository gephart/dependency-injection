<?php

require_once __DIR__ . '/../vendor/autoload.php';

class A
{
    public function hello(string $world): string
    {
        return "hello " . $world;
    }
}

class B
{
    private $a;

    public function __construct(A $a)
    {
        $this->a = $a;
    }

    public function render()
    {
        return $this->a->hello("world");
    }
}

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testDependecies()
    {
        $container = new \Gephart\DependencyInjection\Container();
        $b = $container->get(B::class);
        $this->assertEquals("hello world", $b->render());
    }
}
