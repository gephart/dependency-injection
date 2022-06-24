<?php

namespace Gephart\DependencyInjection;

use Psr\Container\ContainerInterface;
use InvalidArgumentException;
use ReflectionClass;

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
     * @var array<string, mixed>
     */
    private $objects = [];

    /**
     * Get instance of object by class name.
     *
     * @return $this|mixed
     * @throws ContainerException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function get(string $id)
    {
        if ($id === self::class) {
            return $this;
        }

        if (!array_key_exists($id, $this->objects)) {
            if (!class_exists($id)) {
                throw new NotFoundException("Class not found.");
            }

            try {
                $dependencies = $this->getDependencies($id);
                $this->objects[$id] = new $id(...$dependencies);
            } catch (NotFoundException $e) {
                throw new ContainerException(
                    "Container could not initialize '$id' because dependencies not founds."
                );
            } catch (\Exception $exception) {
                throw $exception;
            }
        }

        return $this->objects[$id];
    }

    /**
     * Does the container contain an instance of object?
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        if (isset($this->objects[$id]) || class_exists($id)) {
            return true;
        }
        return false;
    }

    /**
     * @since 0.5
     *
     * @param object $object
     * @param string|null $id
     * @return Container
     */
    public function register($object, ?string $id): Container
    {
        if (!$id) {
            $id = get_class($object);
        }

        if (!is_object($object) || !$object instanceof $id) {
            throw new InvalidArgumentException("Parameter \$object must be instance of object " . $id);
        }

        $this->objects[$id] = $object;

        return $this;
    }

    /**
     * @since 0.5 ContainerException when not found class by parameter of reflection
     * @since 0.4 Now throw ContainerException
     * @since 0.2
     *
     * @param string $id
     * @param string $methodName
     * @return array<int, mixed>
     * @throws ContainerException
     */
    private function getDependencies(string $id, string $methodName = "__construct"): array
    {
        $dependencies = [];
        if (!class_exists($id)) {
            throw new ContainerException("Class $id not exist.");
        }

        $reflectionClass = new ReflectionClass($id);

        if (!$reflectionClass->hasMethod($methodName)) {
            return $dependencies;
        }

        $parameters = $reflectionClass->getMethod($methodName)->getParameters();
        foreach ($parameters as $parameter) {
            try {
                $class = $parameter->getClass();
            } catch (\Exception $exception) {
                throw new ContainerException(
                    "Class not found in $id::$methodName. " . $exception->getMessage()
                );
            }

            if (!$class) {
                throw new ContainerException("All parameters of $id::$methodName must be a class.");
            }

            $dependencies[] = $this->get($class->name);
        }

        return $dependencies;
    }
}
