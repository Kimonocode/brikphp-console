<?php 

namespace Brikphp\Console\Brik;

use Brikphp\Console\Container\DiContainer;

/**
 * Gère le fichier brik.yml du module
 */
class BrikConfig {

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
     * Renvoie la référence clé
     * @return string
     */
    public function getDiInjectionKey(): string
    {
        return trim($this->config['di']['injection']['from']);
    }

    /**
     * Renvoie la valeur clé
     * @return string
     */
    public function getDiInjectionValue(): string 
    {
        return trim($this->config['di']['injection']['to']); 
    }

    /**
     * Renvoie la fonction utilisée pour php-di
     * @return string
     */
    public function getDiInjectionFunction(): string
    {
        return trim($this->config['di']['injection']['function']);
    }

    /**
     * Renvoie si le module est requis dans le container
     * @return bool
     */
    public function isRequiredInDiContainer(): bool
    {
        return $this->config['di']['required'];
    }

    /**
     * Injecte Les dépendances du module dans le container php-di
     * 
     * @throws \RuntimeException
     * @return bool
     */
    public function injectInDiContainer(): bool
    {
        $container = new DiContainer();
        
        $function = $this->getDiInjectionFunction();
        $from = $container->formatClassReference($this->getDiInjectionKey());
        $to = $container->formatClassReference($this->getDiInjectionValue());

        foreach ($container->data() as $key => $value) {
            if($key === $container->removeClassReference($from)){
                throw new \RuntimeException("La dépendance est déjà initialisée dans le container.");
            }
        }

        // Vérification des valeurs pour la méthode DI
        if (!$container->acceptInjectionFunction($function)) {
            throw new \RuntimeException("La méthode DI '{$function}' n'est pas valide.");
        }

        // Ajouter la nouvelle configuration au tableau
        $container->add($from, $container->formatWithInjectionFunction($function, $to));

        return $container->write();
    }
} 