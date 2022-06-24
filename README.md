Gephart Dependecy Injection
===

[![php](https://github.com/gephart/dependency-injection/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/gephart/dependency-injection/actions)

Dependencies
---
 - PHP >= 7.4
 - psr/container == 2.0.2

Instalation
---

```bash
composer require gephart/dependency-injection
```

Using
---

```php
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

$container = new \Gephart\DependencyInjection\Container();
$b = $container->get(B::class);
$b->render(); // hello world
```
