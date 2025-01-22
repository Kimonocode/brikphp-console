<?php

namespace Brikphp\Console\Command\Base;

use Brikphp\Console\Console;
use Brikphp\FileSystem\FileInterface;
use Brikphp\FileSystem\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakerCommand extends BaseCommand
{
    /**
     * Generate a capitalized argument name and append a suffix.
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input Input interface to get arguments.
     * @param string $name The suffix to append to the argument name.
     * @example "users" in the console + makeArgumentName($input, 'Controller') -> "UsersController"
     * @return string The generated name.
     */
    public function makeArgumentName(InputInterface $input, string $name): string
    {
        return ucfirst($input->getArgument('name')) . $name;
    }

    /**
     * Build a namespace by appending a suffix to the user's namespace defined in Composer.
     * 
     * @param string $namespace The suffix namespace to append.
     * @return string The full namespace.
     */
    public function makeNamespace(string $namespace): string
    {
        return Console::getUserNamespace() . $namespace;
    }

    /**
     * Create the file path where the new resource will be saved.
     * 
     * @param string $path The relative path for the new resource.
     * @return string The full file path.
     */
    public function makeFilePath(string $path): string
    {
        return Console::root() . FileSystem::normalizePath("src/{$path}.php");
    }

    /**
     * Create the directory path where a resource will be saved.
     * 
     * @param string $path The relative directory path for the resource.
     * @return string The full directory path.
     */
    public function makeDirPath(string $path): string
    {
        return Console::root() . FileSystem::normalizePath("src/{$path}");
    }

    /**
     * Ensure the directory exists for the resource, or create it if it doesn't exist.
     * 
     * @param string $resource The type of resource being created (e.g., "Controller").
     * @param string $file The directory path.
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output interface for displaying messages.
     * @return int Command status: SUCCESS, INVALID, or FAILURE.
     */
    public function makeDirForResourceOrFail(string $resource, string $file, OutputInterface $output)
    {
        try {
            if (!is_dir($file) && !mkdir($file, 0755, true)) {
                $output->writeln("Error: Failed to create directory for $resource at '$file'");
                return Command::INVALID;
            }
        } catch (\Exception $e) {
            $output->writeln("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    /**
     * Check if the resource already exists and return an error if it does.
     * 
     * @param string $resource The type of resource being checked.
     * @param FileInterface $file The file interface to check the existence.
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output interface for displaying messages.
     * @return int Command status: INVALID if the resource exists, SUCCESS otherwise.
     */
    public function verifyResourceExistsOrFail(string $resource, FileInterface $file, OutputInterface $output)
    {
        if ($file->exists()) {
            $path = $file->getPath();
            $output->writeln("<comment>$resource already exists in '$path'.</comment>");
            return Command::INVALID;
        }
        return Command::SUCCESS;
    }

    /**
     * Display a success message when a new resource has been created.
     * 
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output interface for displaying messages.
     * @param string $resource The type of resource created.
     * @param string $path The path where the resource was written.
     * @return int
     */
    public function endMessage(OutputInterface $output, string $resource, string $path): int
    {
        return $this->success($output, "<info>New $resource written at: $path</info>");
    }

    /**
     * Write the content of a resource to a file, or fail if the operation is unsuccessful.
     * 
     * @param string $content The content to write to the file.
     * @param string $file The file path where the content will be written.
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output interface for displaying messages.
     * @return int Command status: FAILURE if writing fails, SUCCESS otherwise.
     */
    public function createResourceOrFail(string $content, string $file, OutputInterface $output)
    {
        try {
            if (file_put_contents($file, $content) === false) {
                $output->writeln("Error: Failed to write file: $file");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $output->writeln("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    /**
     * Set the configuration for a "make:*" command.
     * 
     * @param string $resource The type of resource to create (e.g., "controller").
     * @param string $folder The folder where the resource will be saved.
     * @return void
     */
    public function setConfiguration(string $resource, string $folder)
    {
        $capitalize = ucfirst($resource);
        $this->setName("make:$resource")
            ->setDescription("Make a new $capitalize")
            ->setHelp("This command creates a new $capitalize inside the $folder folder.")
            ->addArgument('name', \Symfony\Component\Console\Input\InputArgument::REQUIRED);
    }
}
