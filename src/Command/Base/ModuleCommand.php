<?php

namespace Brikphp\Console\Command\Base;

use Brikphp\Console\Brik\BrikConfig;
use Brikphp\Console\Brik\BrikConfigInterface;
use Brikphp\Console\Console;
use Brikphp\Console\FileSystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

class ModuleCommand extends Command
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
    protected function pathToModule(string $module): string
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
    protected function hasBrikDefinitions(string $module): bool 
    {
        $brik = new File($this->pathToModule($module));
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
    protected function itsAnAvailableModule(string $module)
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
     * @return bool|\Brikphp\Console\Brik\BrikConfigInterface
     */
    protected function loadBrikConfig(string $module): bool|BrikConfigInterface
    {
        $brikConfig = Yaml::parseFile($this->pathToModule($module));
        if (
               !isset($brikConfig['di']['required']) 
            || !isset($brikConfig['di']['method']) 
            || !isset($brikConfig['di']['from']) 
            || !isset($brikConfig['di']['to'])) 
        {
            return false;
        }
        return new BrikConfig($brikConfig);
    }
}
