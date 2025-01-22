<?php 

namespace Brikphp\Console;

use Brikphp\Console\Command\Maker\MakeControllerCommand;
use Brikphp\Console\Command\StartServerCommand;
use Symfony\Component\Console\Application;
use Brikphp\Console\Command\Project\ConfigureCommand;
use Brikphp\Console\Command\Module\AddModuleCommand;
use Brikphp\Console\Command\Module\ConfigureModuleCommand;

class Console extends Application
{
    /**
     * Namespace du package BrikPhp
     * @var string
     */
    protected static string $namespace = 'kimonocode/brikphp';

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
        $this->add(new StartServerCommand());
        $this->add(new ConfigureCommand());
        $this->add(new AddModuleCommand());
        $this->add(new ConfigureModuleCommand());
        $this->add(new MakeControllerCommand());
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

    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    public static function getUserNamespace(): string
    {
        $file = new \Brikphp\FileSystem\File(self::root() . 'composer.json');
        $fileSystem = new \Brikphp\FileSystem\FileSystem([$file]);
        $namespace = 'App\\';
        if($file->exists()){
            $content = json_decode($fileSystem->read($file), true);
            $namespace = array_keys($content['autoload']['psr-4'])[0];
        }
        return $namespace;
    }
}
