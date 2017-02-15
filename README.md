Gephart Dependecy Injection
===

[![Build Status](https://travis-ci.org/gephart/dependency-injection.svg?branch=master)](https://travis-ci.org/gephart/dependency-injection)

Dependencies
---
 - PHP >= 7.0

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
