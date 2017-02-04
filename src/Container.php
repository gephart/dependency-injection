<?php

namespace Gephart\DependencyInjection;

final class Container
{
    private $objects = [];

    public function get(string $class_name)
    {
        if ($class_name === self::class) {
            return $this;
        }

        if (!array_key_exists($class_name, $this->objects)) {
            $dependencies = $this->getDependencies($class_name);
            $this->objects[$class_name] = new $class_name(...$dependencies);
        }

        return $this->objects[$class_name];
    }

    private function getDependencies(string $class_name, string $method_name = "__construct"): array
    {
        $dependencies = [];

        $reflection_class = new \ReflectionClass($class_name);

        if (!$reflection_class->hasMethod($method_name)) {
            return $dependencies;
        }

        $parameters = $reflection_class->getMethod($method_name)->getParameters();
        foreach ($parameters as $parameter) {
            if ($parameter->getClass()) {
                $dependencies[] = $this->get($parameter->getClass()->name);
            } else {
                throw new \Exception("Depencency Injection: All parameters of $class_name::$method_name must be a class.");
            }
        }

        return $dependencies;
    }
}
