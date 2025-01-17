<?php

namespace Brikphp\Console\Command\Base;

use Brikphp\Console\Brik\BrikConfig;
use Brikphp\Console\Console;
use Brikphp\Console\FileSystem\File;
use Symfony\Component\Yaml\Yaml;

class ModuleCommand extends BaseCommand
{
    /**
     * Modules valides
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
     * Retourne le chemin du fichier brik.yml du module
     * 
     * @param string $module
     * @return string
     */
    protected function pathToBrik(string $module): string
    {
        $namespace = Console::getNamespace();
        return Console::root() . "vendor/{$namespace}-{$module}/brik.yml";
    }

    /**
     * Vérifie si le module à sa configuration brik.yml
     * 
     * @param string $module Nom du module
     * @return bool
     */
    protected function hasDefinitions(string $module): bool 
    {
        $brik = new File($this->pathToBrik($module));
        if ($brik->exists()) {
            return true;
        }
        return false;
    }

    /**
     * Vérifie si le module passé en argument des commandes de modules se trouve dans la liste des module disponible.
     * 
     * @param string $module nom de module
     * @return bool
     */
    protected function available(string $module)
    {
        if (in_array(strtolower($module), $this->modulesAvailable)) {
            return true;
        }
        return false;
    }

    /**
     * Charge la configuration du module depuis son fichier brik.yml
     * 
     * @param string $module
     * @return bool|\Brikphp\Console\Brik\BrikConfig
     */
    protected function load(string $module): bool|BrikConfig
    {
        $brikConfig = Yaml::parseFile($this->pathToBrik($module));
        if (!isset($brikConfig['di']['required'])){
            return false;
        }
        return new BrikConfig($brikConfig);
    }

    /**
     * Télécharge un module via composer
     * 
     * @param string $module
     * @return bool|string|null
     */
    protected function download(string $module)
    {
        $namespace = Console::getNamespace();
        $version = Console::debug() ? "main-dev" : '';
        $command = "composer require {$namespace}-{$module} {$version}";
        return shell_exec($command);
    }
}
