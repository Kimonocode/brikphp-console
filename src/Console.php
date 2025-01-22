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
     * Namespace for the BrikPhp package.
     * @var string
     */
    protected static string $namespace = 'kimonocode/brikphp';

    /**
     * Development mode flag.
     * @var bool
     */
    protected static bool $dev = true;

    /**
     * Console application name.
     * @var string
     */
    private string $name;

    /**
     * Console application version.
     * @var string
     */
    private string $version;

    /**
     * Constructor that initializes the console with commands.
     * 
     * @param string $name The name of the console application.
     * @param string $version The version of the console application.
     */
    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);

        // Add commands to the console application
        $this->add(new StartServerCommand());
        $this->add(new ConfigureCommand());
        $this->add(new AddModuleCommand());
        $this->add(new ConfigureModuleCommand());
        $this->add(new MakeControllerCommand());
    }

    /**
     * Returns the current working directory path.
     * 
     * @return string The path of the current working directory.
     */
    public static function root(): string
    {
        return getcwd() . DIRECTORY_SEPARATOR;
    }

    /**
     * Checks if the application is in development mode.
     * 
     * @return bool True if in development mode, false otherwise.
     */
    public static function debug(): bool
    {
        return self::$dev;
    }

    /**
     * Returns the namespace for the BrikPhp package.
     * 
     * @return string The namespace of the BrikPhp package.
     */
    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    /**
     * Retrieves the user-defined namespace from the composer.json file.
     * 
     * @return string The user's namespace defined in the composer.json file.
     */
    public static function getUserNamespace(): string
    {
        // Path to the composer.json file.
        $file = new \Brikphp\FileSystem\File(self::root() . 'composer.json');
        $fileSystem = new \Brikphp\FileSystem\FileSystem([$file]);

        // Default namespace is 'App\\'.
        $namespace = 'App\\';

        // Check if the composer.json file exists and extract the namespace if available.
        if ($file->exists()) {
            $content = json_decode($fileSystem->read($file), true);
            $namespace = array_keys($content['autoload']['psr-4'])[0];
        }

        return $namespace;
    }
}
