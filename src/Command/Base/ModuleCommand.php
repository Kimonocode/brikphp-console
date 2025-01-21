<?php

namespace Brikphp\Console\Command\Base;

use Brikphp\Console\Brik\BrikConfig;
use Brikphp\Console\Console;
use Brikphp\FileSystem\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides shared functionality for managing application modules.
 * This class supports checking module validity, verifying module configuration,
 * and interacting with module configuration files.
 */
class ModuleCommand extends BaseCommand
{
    /**
     * List of valid modules.
     * 
     * @var string[]
     */
    protected array $modulesAvailable = [
        'validator',
        'logger',
        'database',
        'mailer',
        'session',
        'renderer',
    ];

    /**
     * Retrieves the path to a module's `brik.yml` configuration file.
     *
     * @param string $module The module name.
     * @return string The path to the module's `brik.yml` file.
     */
    protected function pathToBrik(string $module): string
    {
        $namespace = Console::getNamespace();
        return Console::root() . "vendor/{$namespace}-{$module}/brik.yml";
    }

    /**
     * Checks if a module has a `brik.yml` configuration file.
     *
     * @param string $module The module name.
     * @return bool True if the file exists, otherwise false.
     */
    protected function hasDefinitions(string $module): bool 
    {
        $brik = new File($this->pathToBrik($module));
        return $brik->exists();
    }

    /**
     * Validates if a module is included in the list of available modules.
     *
     * @param string $module The module name.
     * @return bool True if the module is available, otherwise false.
     */
    protected function available(string $module): bool
    {
        return in_array(strtolower($module), $this->modulesAvailable, true);
    }

    /**
     * Parses the `brik.yml` configuration file for a module.
     *
     * @param string $module The module name.
     * @return bool|BrikConfig The parsed configuration as a BrikConfig object, or false on failure.
     */
    protected function load(string $module): bool|BrikConfig
    {
        $brikConfig = Yaml::parseFile($this->pathToBrik($module));
        if (!isset($brikConfig['di']['required'])) {
            return false;
        }
        return new BrikConfig($brikConfig);
    }

    /**
     * Downloads a module using Composer.
     *
     * @param string $module The module name.
     * @return bool|string|null The shell output of the Composer command, or null on failure.
     */
    protected function download(string $module)
    {
        $namespace = Console::getNamespace();
        $version = Console::debug() ? "main-dev" : '';
        $command = "composer require {$namespace}-{$module} {$version}";
        return shell_exec($command);
    }
}
