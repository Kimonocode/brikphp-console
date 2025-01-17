<?php

namespace Brikphp\Console\Brik;

interface BrikConfigInterface {
 
    /**
     * Renvoie la Clé de référence de l'injection
     * 
     * @return string
     */
    public function getDiInjectionKey(): string;

    /**
     * Renvoie la valeur de référence de l'injection
     * 
     * @return string
     */
    public function getDiInjectionValue(): string;

    /**
     * Renvoie la fonction DI à utiliser pour l'injection
     * 
     * @return string
     */
    public function getDiInjectionFunction(): string;
    
    /**
     * Renvoie si le module est nécéssaire dans le container DI
     * 
     * @return bool
     */
    public function isRequiredInDiContainer();

    /**
     * Essaie d'ajouter le module au container
     * @throws \RuntimeException
     * @return bool
     */
    public function tryAddInDiContainer(): bool;
}