<?php

namespace Brikphp\Console\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface {

    /**
     * Container vide
     * @var array
     */
    private array $container = [];

    /**
     * Ajoue une nouvelle clé / valeur
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function add(string $key, mixed $value) 
    {
        $this->container[$key] = $value;
    }
    
    /**
     * @inheritDoc
     */
    public function has(string $key): bool 
    {
        return array_key_exists($key, $this->container);
    }

    /**
     * Modifie le container
     * 
     * @param ContainerInterface|array $container
     * @return void
     */
    public function set(ContainerInterface|array $container): void 
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function get(string $id) {
    }

    /**
     * Renvoie les donées du container
     * @return array
     */
    public function data(): array 
    {
        return $this->container;
    }
}