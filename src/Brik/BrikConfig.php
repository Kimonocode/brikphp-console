<?php 

namespace Brikphp\Console\Brik;

use Brikphp\Console\Container\DiContainer;

/**
 * Gère le fichier brik.yml du module
 */
class BrikConfig implements BrikConfigInterface {

    /**
     * Configuration vide
     * @var array
     */
    private array $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getDiInjectionKey(): string
    {
        return trim($this->config['di']['injection']['from']);
    }

    /**
     * @inheritDoc
     */
    public function getDiInjectionValue(): string 
    {
        return trim($this->config['di']['injection']['to']); 
    }

    /**
     * @inheritDoc
     */
    public function getDiInjectionFunction(): string
    {
        return trim($this->config['di']['injection']['function']);
    }
    
    /**
     * @inheritDoc
     */
    public function isRequiredInDiContainer(): bool
    {
        return $this->config['di']['required'];
    }

     /**
     * @inheritDoc
     */
    public function tryAddInDiContainer(): bool
    {
        $container = new DiContainer();

        $function = $this->getDiInjectionFunction();
        $from = $container->formatClassReference($this->getDiInjectionKey());
        $to = $container->formatClassReference($this->getDiInjectionValue());

        // Vérification des valeurs pour la méthode DI
        if ($container->acceptInjectionFunction($function)) {
            throw new \RuntimeException("La méthode DI '{$function}' n'est pas valide.");
        }

        // Vérifier si la clé existe déjà
        if ($container->has($from)) {
            throw new \RuntimeException("La clé {$from} existe déjà dans le container.");
        }

        // Ajouter la nouvelle configuration au tableau
        $container->add($from, $container->formatWhitInjectionFunction($function, $to));

        return $container->write();
    }
} 