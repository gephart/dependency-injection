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

class C
{
    public function __construct($id) {}
}

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    private $container;

    public function setUp()
    {
        $this->container = new \Gephart\DependencyInjection\Container();
    }

    public function testGet()
    {
        $b = $this->container->get(B::class);
        $this->assertEquals("hello world", $b->render());
    }

    public function testHas()
    {
        $result = $this->container->has(B::class);
        $this->assertTrue($result);

        $result = $this->container->has("NonExistsClass");
        $this->assertFalse($result);
    }

    public function testNotFoundException()
    {
        $exception_name_result = false;

        try {
            $this->container->get("NonExistsClass");
        } catch (\Gephart\DependencyInjection\NotFoundException $e) {
            $exception_name_result = true;
        }

        $this->assertTrue($exception_name_result);
    }

    public function testException()
    {
        $exception_name_result = false;

        try {
            $this->container->get(C::class);
        } catch (\Gephart\DependencyInjection\ContainerException $e) {
            $exception_name_result = true;
        }

        $this->assertTrue($exception_name_result);
    }

    public function testPsrNotFoundException()
    {
        $exception_name_result = false;

        try {
            $this->container->get("NonExistsClass");
        } catch (\Psr\Container\NotFoundExceptionInterface $e) {
            $exception_name_result = true;
        }

        $this->assertTrue($exception_name_result);
    }

    public function testPsrException()
    {
        $exception_name_result = false;

        try {
            $this->container->get(C::class);
        } catch (\Psr\Container\ContainerExceptionInterface $e) {
            $exception_name_result = true;
        }

        $this->assertTrue($exception_name_result);
    }
}
