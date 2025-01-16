<?php

namespace Brikphp\Console\Command;

use Brikphp\Console\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddModuleCommand extends Command {

    protected function configure() {
        $this->setName('brik:add')
            ->setDescription("Ajoute un nouveau module à votre application.")
            ->setHelp("Cette commande ajoute un nouveau module à votre application.")
            ->addArgument('module', InputArgument::REQUIRED, 'Nom du module.');

    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $module = strtolower($input->getArgument('module'));
        $version = Console::debug() ? "main-dev" : '';
        $command = "composer require kimonocode/brikphp-{$module} {$version}";
        $output->writeln("\nAjout du module {$module} ...\n");

        if(!shell_exec($command)){
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}