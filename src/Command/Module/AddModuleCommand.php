<?php

namespace Brikphp\Console\Command\Module;

use Brikphp\Console\Command\Base\ModuleCommand;
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

    protected function execute(InputInterface $input, OutputInterface $output): int 
    {      
        $module = $input->getArgument('module');

        // vérifie si le module est valide
        if(!$this->available(module: $module)) {
            $output->writeln("\n<error>ERROR</error> Le module '{$module}' est invalide.\n");
            return Command::INVALID;
        }

        // vérifie si le module est déjà installé
        if($this->hasDefinitions($module)){
            $output->writeln("\n<info>INFO</info> Le module '{$module}' est déjà installé.\n");
            return Command::INVALID;
        }

        $output->writeln("\nAjout du module {$module} ...\n");

        if(!$this->download($module)) {
            $output->writeln("\n<error>ERROR</error> Impossible de télécharger le module.n");
            return Command::FAILURE;
        }

        $output->writeln("\nLe module a été installé. Essayez brik:configure <module> pour l'initialiser.\n");

        return Command::SUCCESS;
    }

}