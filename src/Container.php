<?php

namespace Gephart\DependencyInjection;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Dependency injection container
 *
 * @package Gephart\DependencyInjection
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.2
 */
final class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $objects = [];

    /**
     * Get instance of object by class name.
     *
     * @param string $class_name
     * @return $this|mixed
     * @throws ContainerException
     * @throws NotFoundException
     * @throws \Exception
     */
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
            } catch (\Exception $exception) {
                throw $exception;
            }
        }

        return $this->objects[$class_name];
    }

    /**
     * Does the container contain an instance of object?
     *
     * @param string $class_name
     * @return bool
     */
    public function has($class_name): bool
    {
        if (isset($this->objects[$class_name]) || class_exists($class_name)) {
            return true;
        }
        return false;
    }

    /**
     * @since 0.5
     *
     * @param $object
     * @param string|null $class_name
     * @return Container
     */
    public function register($object, string $class_name = null): Container
    {
        if (!$class_name) {
            $class_name = get_class($object);
        }

        if (!is_object($object) || !$object instanceof $class_name) {
            throw new \InvalidArgumentException("Parameter \$object must be instance of object " . $class_name);
        }

        $this->objects[$class_name] = $object;

        return $this;
    }

    /**
     * @since 0.5 ContainerException when not found class by parameter of reflection
     * @since 0.4 Now throw ContainerException
     * @since 0.2
     *
     * @param string $class_name
     * @param string $method_name
     * @return array
     * @throws ContainerException
     */
    private function getDependencies(string $class_name, string $method_name = "__construct"): array
    {
        $dependencies = [];

        $reflection_class = new \ReflectionClass($class_name);

        if (!$reflection_class->hasMethod($method_name)) {
            return $dependencies;
        }

        $parameters = $reflection_class->getMethod($method_name)->getParameters();
        foreach ($parameters as $parameter) {
            try {
                $class = $parameter->getClass();
            } catch (\Exception $exception) {
                throw new ContainerException("Class not found in $class_name::$method_name. " . $exception->getMessage());
            }

            if ($class) {
                $dependencies[] = $this->get($class->name);
            } else {
                throw new ContainerException("All parameters of $class_name::$method_name must be a class.");
            }
        }

        return $dependencies;
    }
}
