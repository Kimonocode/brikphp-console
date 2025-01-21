<?php

namespace Brikphp\Console\Command\Project;

use Brikphp\Console\Command\Base\ModuleCommand;
use Brikphp\Console\Console;
use Brikphp\Console\Container\ProjectContainer;
use Brikphp\FileSystem\File;
use Brikphp\FileSystem\FileInterface;
use Brikphp\FileSystem\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * ConfigureCommand handles the interactive configuration of the project.
 */
class ConfigureCommand extends ModuleCommand
{
    /**
     * List of supported databases.
     * @var string[]
     */
    private array $databasesAvailable = ['MySQL', 'PostgreSQL', 'MongoDB'];

    /**
     * Configures the command with its name, description, and help text.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:configure')
            ->setDescription('Interactive configuration of the project.')
            ->setHelp('This command helps configure your project by collecting the necessary information.');
    }

    /**
     * Executes the command to configure the project.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->askForDeleteCurrentConfig($input, $output);

        // Project configuration
        $project = new ProjectContainer();
        $project->add('mode', 'development');
        $project->add('APP_NAME', $this->askProjectName($input, $output) ?: 'Demo');
        $project->add('APP_SECRET', bin2hex(random_bytes(16)));

        // Database configuration
        $useDb = $this->yesOrNo('Use a database?', $input, $output);

        if ($useDb) {
            $project->add('DB_CLIENT', $this->askDatabaseClient($input, $output) ?: 'MySQL');
            $project->add('DB_HOST', $this->askDatabaseHost($input, $output) ?: '127.0.0.1');
            $project->add('DB_USER', $this->askDatabaseUser($input, $output) ?: 'root');
            $project->add('DB_NAME', $this->askDatabaseName($input, $output) ?: 'demo');
            $project->add('DB_PASS', $this->askDatabasePassword($input, $output) ?: '');
        }

        return $this->saveEnvironment($output, $project);
    }

    /** 
     * Saves the environment configuration to a .env file.
     *
     * @param OutputInterface $output
     * @param ProjectContainer $project
     * @throws \RuntimeException If the file cannot be created or written to.
     * @return int
     */
    private function saveEnvironment(OutputInterface $output, ProjectContainer $project): int
    {
        $fileSystem = new FileSystem();
        if (!$fileSystem->create($this->envFile())) {
            $output->writeln("<error>Unable to create the .env file.</error>");
            return Command::FAILURE;
        }

        $output->writeln("\n<info>Writing variables to the .env file...</info>");

        foreach ($project->data() as $key => $value) {
            $formattedValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $formattedValue = str_contains($formattedValue, ' ') ? "\"{$formattedValue}\"" : $formattedValue;

            if (!$fileSystem->write($this->envFile(), "{$key}={$formattedValue}\n")) {
                $output->writeln("<error>Error writing {$key} to the .env file.</error>");
                return Command::FAILURE;
            }
        }

        $output->writeln("<info>Configuration saved successfully.</info>");
        $output->writeln("\n<info>Configuration summary:</info>\n");

        foreach ($project->data() as $key => $value) {
            $valueDisplay = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $output->writeln("<comment>{$key}</comment>=<info>{$valueDisplay}</info>");
        }

        $output->writeln("\nYou can now start the application with the command <comment>composer dev</comment>\n");
        return Command::SUCCESS;
    }

    /**
     * Asks the user whether to delete the current configuration.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    private function askForDeleteCurrentConfig(InputInterface $input, OutputInterface $output): void
    {
        $fileSystem = new FileSystem();
        if ($this->envFile()->exists()) {
            $delete = $this->yesOrNo(
                "The .env configuration file already exists. Delete the file?",
                $input,
                $output
            );

            if ($delete) {
                $fileSystem->delete($this->envFile());
            } else {
                exit(0);
            }
        }
    }

    /**
     * Prompts the user for the project name.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string|null
     */
    private function askProjectName(InputInterface $input, OutputInterface $output): ?string
    {
        return $this->ask("\nEnter a name for your project (Demo): ", $input, $output);
    }

    /**
     * Prompts the user for the database client type.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function askDatabaseClient(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Which database type would you like to use? (default MySQL)',
            $this->databasesAvailable,
            0
        );
        $question->setErrorMessage('The database type "%s" is invalid.');
        return $helper->ask($input, $output, $question);
    }

    /**
     * Prompts the user for the database name.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string|null
     */
    private function askDatabaseName(InputInterface $input, OutputInterface $output): ?string
    {
        return $this->ask("Database name (default demo): ", $input, $output);
    }

    /**
     * Prompts the user for the database username.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string|null
     */
    private function askDatabaseUser(InputInterface $input, OutputInterface $output): ?string
    {
        return $this->ask("Database username (default root): ", $input, $output);
    }

    /**
     * Prompts the user for the database password.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string|null
     */
    private function askDatabasePassword(InputInterface $input, OutputInterface $output): ?string
    {
        return $this->ask("Database password (optional): ", $input, $output);
    }

    /**
     * Prompts the user for the database host.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string|null
     */
    private function askDatabaseHost(InputInterface $input, OutputInterface $output): ?string
    {
        return $this->ask("Database host (default localhost): ", $input, $output);
    }

    /**
     * Returns a File object pointing to the .env file.
     *
     * @return FileInterface
     */
    private function envFile(): FileInterface
    {
        return new File(Console::root() . '.env');
    }
}
