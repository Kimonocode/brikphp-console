<?php

namespace Brikphp\Console\Command\Module;

use Brikphp\Console\Command\Base\ModuleCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigureModuleCommand extends ModuleCommand
{
    /**
     * Configuration de la commande
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('brik:configure')
            ->setDescription("Configure un nouveau module ajouté à votre application.")
            ->setHelp("Cette commande configure un nouveau module ajouté à votre application.")
            ->addArgument('module', InputArgument::REQUIRED, 'Nom du module.');
    }

    /**
     * Main Function
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \RuntimeException
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // vérifie si le module se trouve dans la liste de module valide
        $module = $input->getArgument('module');
        if(!$this->itsAnAvailableModule(module: $module)) {
            $output->writeln("\n<error>ERROR</error> Le module '{$module}' est invalide.\n");
            return Command::FAILURE;
        }

        // vérifie si le module est correctement installé.
        if(!$this->hasBrikDefinitions($module)){
            $output->writeln("\n<error>ERROR</error> Le module '{$module}' n'est pas installé.\n");
            return Command::FAILURE;
        }

        $brikConfig = $this->loadBrikConfig($module);
        if (!$brikConfig) {
            $output->writeln("<error>ERROR</error> La configuration DI est invalide.");
            return Command::FAILURE;
        }

        $output->writeln("\nConfiguration du module {$module} ...\n");

        // Ajoute le module dans le container d'injections de dépendance si besoin
        if ($brikConfig->isRequiredInDiContainer()) {
            if (!$brikConfig->tryAddInDiContainer()) {
                throw new \RuntimeException("Impossible d'initialiser le module dans le container.");
            }
        }

        $output->writeln("\n<info>Le module {$module} à été initialisé.</info>\n");
        return Command::SUCCESS;
    }

}