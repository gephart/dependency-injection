<?php

namespace Gephart\DependencyInjection;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    private $objects = [];

    public function get($class_name)
    {
        if ($class_name === self::class) {
            return $this;
        }

        if (!array_key_exists($class_name, $this->objects)) {
            if (!class_exists($class_name)) {
                throw new NotFoundException("Class not found.");
            }

            try {
                $dependencies = $this->getDependencies($class_name);
                $this->objects[$class_name] = new $class_name(...$dependencies);
            } catch (NotFoundException $e) {
                throw new ContainerException("Container could not initialize '$class_name' because dependencies not founds.");
            } catch (\Exception $e) {
                throw new ContainerException("Container could not initialize '$class_name' because: ." . $e->getMessage());
            }
        }

        return $this->objects[$class_name];
    }

    public function has($class_name): bool
    {
        if (isset($this->objects[$class_name]) || class_exists($class_name)) {
            return true;
        }
        return false;
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
                throw new \Exception("All parameters of $class_name::$method_name must be a class.");
            }
        }

        return $dependencies;
    }
}
