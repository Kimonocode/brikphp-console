<?php

namespace Brikphp\Console\Command\Module;

use Brikphp\Console\Command\Base\ModuleCommand;
use Brikphp\Console\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddModuleCommand extends ModuleCommand {

    protected function configure() {
        $this->setName('brik:add')
            ->setDescription("Ajoute un nouveau module à votre application.")
            ->setHelp("Cette commande ajoute un nouveau module à votre application.")
            ->addArgument('module', InputArgument::REQUIRED, 'Nom du module.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {      
        $module = $input->getArgument('module'); 
        $version = Console::debug() ? "main-dev" : '';
        $command = "composer require kimonocode/brikphp-{$module} {$version}";

        $output->writeln("\nAjout du module {$module} ...\n");

        if(!$this->itsAnAvailableModule(module: $module)) {
            $output->writeln("\n<error>ERROR</error> Le module '{$module}' est invalide.\n");
            return Command::INVALID;
        }

        // vérifie si le module est déjà installé
        if($this->hasBrikDefinitions($module)){
            $output->writeln("\n<info>ERROR</info> Le module '{$module}' est déjà installé.\n");
            return Command::INVALID;
        }

        if(!shell_exec($command)){
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}