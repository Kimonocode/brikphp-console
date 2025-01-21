<?php

namespace Brikphp\Console\Command\Module;

use Brikphp\Console\Command\Base\ModuleCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to add a new module to the application.
 */
class AddModuleCommand extends ModuleCommand {

    /**
     * Configures the command by defining its name, description, help, and arguments.
     */
    protected function configure() {
        $this->setName('brik:add')
            ->setDescription("Adds a new module to your application.")
            ->setHelp("This command adds a new module to your application by downloading and preparing it for use.")
            ->addArgument('module', InputArgument::REQUIRED, 'Name of the module.');
    }

    /**
     * Executes the command to add the specified module.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The command status (success or failure).
     */
    protected function execute(InputInterface $input, OutputInterface $output): int 
    {      
        $module = $input->getArgument('module');

        // Check if the module is valid.
        if (!$this->available(module: $module)) {
            $output->writeln("\n<error>ERROR:</error> The module '{$module}' is invalid.\n");
            return Command::INVALID;
        }

        // Check if the module is already installed.
        if ($this->hasDefinitions($module)) {
            $output->writeln("\n<info>INFO:</info> The module '{$module}' is already installed.\n");
            return Command::INVALID;
        }

        $output->writeln("\nAdding the module '{$module}'...\n");

        // Attempt to download the module.
        if (!$this->download($module)) {
            $output->writeln("\n<error>ERROR:</error> Unable to download the module.\n");
            return Command::FAILURE;
        }

        $output->writeln("\nThe module has been installed. Try running 'brik:configure <module>' to initialize it.\n");

        return Command::SUCCESS;
    }
}
