<?php

namespace Brikphp\Console\Command\Module;

use Brikphp\Console\Command\Base\ModuleCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to configure a new module added to the application.
 */
class ConfigureModuleCommand extends ModuleCommand
{
    /**
     * Configures the command by defining its name, description, help, and arguments.
     */
    protected function configure()
    {
        $this->setName('brik:configure')
            ->setDescription("Configures a new module added to your application.")
            ->setHelp("This command configures a new module added to your application by validating and initializing its setup.")
            ->addArgument('module', InputArgument::REQUIRED, 'Name of the module.');
    }

    /**
     * Executes the command to configure the specified module.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The command status (success or failure).
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module = strtolower($input->getArgument('module'));

        // Check if the module is in the list of valid modules.
        if (!$this->available(module: $module)) {
            $output->writeln("\n<error>ERROR:</error> The module '{$module}' is invalid.\n");
            return Command::FAILURE;
        }

        // Verify if the module is properly installed.
        if (!$this->hasDefinitions($module)) {
            $output->writeln("\n<error>ERROR:</error> The module '{$module}' does not have a 'brik.yml' file in its root directory.\n");
            return Command::FAILURE;
        }

        // Load the brik.yml configuration file.
        $brikConfig = $this->load($module);
        if (!$brikConfig) {
            $output->writeln("<error>ERROR:</error> The 'brik.yml' configuration is invalid.");
            return Command::FAILURE;
        }

        // Add the module to the dependency injection container if required.
        if ($brikConfig->isRequiredInDiContainer()) {
            if (!$brikConfig->injectInDiContainer()) {
                $output->writeln("<error>ERROR:</error> Unable to initialize the module in the dependency injection container.");
                return Command::FAILURE;
            }
        }

        $output->writeln("\n<info>The module '{$module}' has been successfully initialized.</info>\n");
        return Command::SUCCESS;
    }
}
