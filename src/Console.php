<?php 

namespace Brikphp\Console;

use Brikphp\Console\Command\AddModuleCommand;
use Brikphp\Console\Command\ConfigureCommand;
use Brikphp\Console\Command\ConfigureModuleCommand;
use Symfony\Component\Console\Application;

class Console extends Application
{
    protected static bool $dev = true;

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
        $this->add(new AddModuleCommand());
        $this->add(new ConfigureModuleCommand());
    }

    /**
     * Retourne le chemin du script en cours
     * @return string
     */
    public static function root(): string 
    {
        return getcwd() . DIRECTORY_SEPARATOR;
    }

    public static function debug(): bool
    {
        return self::$dev;
    }

}
