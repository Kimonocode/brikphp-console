<?php

namespace Brikphp\Console\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * Implements a simple Dependency Injection (DI) container following the PSR-11 standard.
 */
class Container implements ContainerInterface
{
    /**
     * Holds the container's key-value data.
     * 
     * @var array<string, mixed>
     */
    private array $container = [];

    /**
     * Adds a new key-value pair to the container.
     * 
     * @param string $key The key to be added.
     * @param mixed $value The value associated with the key.
     * @return void
     */
    public function add(string $key, mixed $value): void
    {
        $this->container[$key] = $value;
    }

    /**
     * Checks if the container contains the specified key.
     * 
     * @param string $key The key to check.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->container);
    }

    /**
     * Replaces the current container data with a new set of data.
     * 
     * @param array<string, mixed>|ContainerInterface $container The new container data.
     * @return void
     */
    public function set(ContainerInterface|array $container): void
    {
        $this->container = $container;
    }

    /**
     * Retrieves an entry from the container by its identifier.
     * 
     * @param string $id The identifier of the entry.
     * @return mixed The value of the entry.
     * @throws NotFoundExceptionInterface If the identifier does not exist in the container.
     * @throws ContainerExceptionInterface For any general error in the container.
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new class($id) extends \RuntimeException implements NotFoundExceptionInterface {
                public function __construct($id)
                {
                    parent::__construct("The key '{$id}' was not found in the container.");
                }
            };
        }

        return $this->container[$id];
    }

    /**
     * Retrieves all the data stored in the container.
     * 
     * @return array<string, mixed> The container's data.
     */
    public function data(): array
    {
        return $this->container;
    }
}
