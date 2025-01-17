<?php

namespace Brikphp\Console\Container;

use Brikphp\Console\Console;

class DiContainer extends Container {

    /**
     * Chemin vers le fichier du container
     * @var string
     */
    private string $path;

    /**
     * namespace brikphp
     * @var string
     */
    private string $namespace;

    /**
     * Méthodes d'injections disponibles
     * @var string[]
     */
    private array $functionsAvailable = ['get', 'create'];

    public function __construct()
    {
        $this->namespace = Console::getNamespace();
        $this->path = Console::root() . "vendor/{$this->namespace}/src/Core/config.php";
        $this->set($this->open());
    }

    /**
     * Ajoute une nouvelle injection dans le container 
     * 
     * @throws \RuntimeException
     * @return bool
     */
    public function write()
    {
        $content = "<?php\n\nreturn [\n";
        foreach ($this->data() as $key => $value) {
            $content .= "    {$this->formatClassReference($key)} => \\DI\\{$this->forceClassReference($value)},\n";
        }
        $content .= "];\n";
        if (file_put_contents($this->path, $content) === false) {
            throw new \RuntimeException("Erreur lors de l'écriture dans le fichier de configuration.");
        }
        return true;
    }

    /**
     * Ajoute ::class à la fin d'une clé si besoin
     * 
     * @param string $key
     * @return string
     */
    public function formatClassReference(string $key): string
    {
        if (substr($key, -7) !== '::class') {
            $key .= '::class';
        }
        return $key;
    }

    /**
     * Supprime la dernière parenthèse de la function php-di, Force le mot clé ::class et rajoute la parenthèse
     * 
     * @param string $key
     * @return string
     */
    public function forceClassReference(string $key): string 
    {
        if (!preg_match('/class/', $key)) {
            $key = substr($key, 0, -1);
            $key .= "::class)";
        }
        return $key;
    }

    /**
     * Supprime le ::class d'une clé au besoin
     * 
     * @param string $key
     * @return string
     */
    public function removeClassReference(string $key): string
    {
        return substr($key, 0, -7);
    }

    /**
     * Retourne true si la méthode d'injection est acceptée false sinon
     * 
     * @param string $function
     * @return bool
     */
    public function acceptInjectionFunction(string $function): bool
    {   
        return in_array($function, $this->functionsAvailable);
    }

    /**
     * Summary of formatWhitInjectionFunction
     * @param string $function
     * @param string $key
     * @throws \RuntimeException
     * @return string
     */
    public function formatWhitInjectionFunction(string $function, string $key): string
    {
        if(!$this->acceptInjectionFunction($function)){
            throw new \RuntimeException("Function invalide pour le container d'injections de dépendances.");    
        }
        return "{$function}({$key})";
    }

    /**
     * Inclu le fichier du container d'injection de dépendance Brikphp
     * 
     * @throws \RuntimeException
     * @return array
     */
    private function open(): array
    {
        $path = $this->path;
        if (!file_exists($path)) {
            throw new \RuntimeException("Le fichier {$path} est introuvable.");
        }
        if (!is_writable($path)) {
            throw new \RuntimeException("Le fichier {$path} n'est pas accessible en écriture.");
        }
        $container = include $path;
           if (!is_array($container)) {
            throw new \RuntimeException("Le fichier de configuration ne retourne pas un tableau valide.");
        }
        return $container;
    }
}