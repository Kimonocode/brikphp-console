<?php 

namespace Brikphp\Console\Brik;

use Brikphp\Console\Container\DiContainer;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function getDiInjectionKey(): string
    {
        return trim($this->config['di']['injection']['from']);
    }

    public function getDiInjectionValue(): string 
    {
        return trim($this->config['di']['injection']['to']); 
    }

    public function getDiInjectionFunction(): string
    {
        return trim($this->config['di']['injection']['function']);
    }

    public function isRequiredInDiContainer(): bool
    {
        return $this->config['di']['required'];
    }

    /**
     * Summary of tryAddInDiContainer
     * @param string $module
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \RuntimeException
     * @return bool
     */
    public function tryAddInDiContainer(): bool
    {
        $container = new DiContainer();
        
        $function = $this->getDiInjectionFunction();
        $from = $container->formatClassReference($this->getDiInjectionKey());
        $clearFrom = substr($from, 0, -7);
        $to = $container->formatClassReference($this->getDiInjectionValue());

        foreach ($container->data() as $key => $value) {
            if($key === $clearFrom){
                throw new \RuntimeException("Déjà installé.");
            }
        }

        // Vérification des valeurs pour la méthode DI
        if (!$container->acceptInjectionFunction($function)) {
            throw new \RuntimeException("La méthode DI '{$function}' n'est pas valide.");
        }

        // Ajouter la nouvelle configuration au tableau
        $container->add($from, $container->formatWhitInjectionFunction($function, $to));

        return $container->write();
    }
} 