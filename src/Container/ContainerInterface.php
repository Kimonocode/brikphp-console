<?php

namespace Brikphp\Console\Container;

/**
 * Interface for a Dependency Injection (DI) Container.
 * 
 * Defines the contract for managing key-value pairs within the container.
 */
interface ContainerInterface
{
    /**
     * Checks if the container has a specific key.
     * 
     * @param string $key The key to check in the container.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Adds a key-value pair to the container.
     * 
     * @param string $key The key to add to the container.
     * @param mixed $value The value associated with the key.
     * @return void
     */
    public function add(string $key, mixed $value): void;

    /**
     * Retrieves all the contents of the container as an array.
     * 
     * @return array An associative array representing the container's contents.
     */
    public function get(): array;

    /**
     * Sets the contents of the container.
     * 
     * Replaces the current container data with the provided array.
     * 
     * @param array $container An associative array to set as the container's data.
     * @return void
     */
    public function set(array $container): void;
}
