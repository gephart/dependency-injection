<?php

namespace Gephart\DependencyInjection;

use Psr\Container\ContainerInterface;

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
     * @param string $className
     * @return $this|mixed
     * @throws ContainerException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function get($className)
    {
        if ($className === self::class) {
            return $this;
        }

        if (!array_key_exists($className, $this->objects)) {
            if (!class_exists($className)) {
                throw new NotFoundException("Class not found.");
            }

            try {
                $dependencies = $this->getDependencies($className);
                $this->objects[$className] = new $className(...$dependencies);
            } catch (NotFoundException $e) {
                throw new ContainerException(
                    "Container could not initialize '$className' because dependencies not founds."
                );
            } catch (\Exception $exception) {
                throw $exception;
            }
        }

        return $this->objects[$className];
    }

    /**
     * Does the container contain an instance of object?
     *
     * @param string $className
     * @return bool
     */
    public function has($className): bool
    {
        if (isset($this->objects[$className]) || class_exists($className)) {
            return true;
        }
        return false;
    }

    /**
     * @since 0.5
     *
     * @param object $object
     * @param string|null $className
     * @return Container
     */
    public function register($object, ?string $className): Container
    {
        if (!$className) {
            $className = get_class($object);
        }

        if (!is_object($object) || !$object instanceof $className) {
            throw new \InvalidArgumentException("Parameter \$object must be instance of object " . $className);
        }

        $this->objects[$className] = $object;

        return $this;
    }

    /**
     * @since 0.5 ContainerException when not found class by parameter of reflection
     * @since 0.4 Now throw ContainerException
     * @since 0.2
     *
     * @param string $className
     * @param string $methodName
     * @return array
     * @throws ContainerException
     */
    private function getDependencies(string $className, string $methodName = "__construct"): array
    {
        $dependencies = [];

        $reflectionClass = new \ReflectionClass($className);

        if (!$reflectionClass->hasMethod($methodName)) {
            return $dependencies;
        }

        $parameters = $reflectionClass->getMethod($methodName)->getParameters();
        foreach ($parameters as $parameter) {
            try {
                $class = $parameter->getClass();
            } catch (\Exception $exception) {
                throw new ContainerException(
                    "Class not found in $className::$methodName. " . $exception->getMessage()
                );
            }

            if (!$class) {
                throw new ContainerException("All parameters of $className::$methodName must be a class.");
            }

            $dependencies[] = $this->get($class->name);
        }

        return $dependencies;
    }
}
