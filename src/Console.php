<?php 

namespace Brikphp\Console;

use Brikphp\Console\Command\ConfigureCommand;
use Symfony\Component\Console\Application;

class Console extends Application
{
    /**
     * Nom de la console
     * @var string
     */
    private string $name;

    /**
     * Version
     * @var string
     */
    private string $version;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
        $this->add(new ConfigureCommand());
    }

    /**
     * Retourne le chemin du script en cours
     * @return string
     */
    public static function root(): string 
    {
        return getcwd() . DIRECTORY_SEPARATOR;
    }

}
