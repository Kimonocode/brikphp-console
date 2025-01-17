<?php

namespace Brikphp\Console\Container;

interface ContainerInterface {

    /**
     * Vérifie si le container contient uné clé
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key);

    /**
     * Ajoute au container
     * 
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function add(string $key, mixed $value);

    /**
     * Retourne le container
     * @return array
     */
    public function get(): array;

    /**
     * Modifie le container
     * 
     * @param array $container
     * @return void
     */
    public function set(array $container): void;

}