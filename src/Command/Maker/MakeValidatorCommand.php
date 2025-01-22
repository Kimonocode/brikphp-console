<?php

namespace Brikphp\Console\Command\Maker;

use Brikphp\FileSystem\File;
use Brikphp\Console\Command\Base\MakerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeValidatorCommand extends MakerCommand
{
    /**
     * Configure the "make:validator" command.
     */
    protected function configure()
    {
        $this->setConfiguration('validator', 'src/Validator');
    }

    /**
     * Execute the "make:validator" command.
     * 
     * @param InputInterface $input The input interface for handling user input.
     * @param OutputInterface $output The output interface for displaying messages.
     * @return int The exit code (SUCCESS or FAILURE).
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $validator = $this->makeArgumentName($input, 'Validator');
        $namespace = $this->makeNamespace('Validator');
        $dir       = $this->makeDirPath("Validator");
        $file      = $this->makeFilePath("Validator/$validator");

        $this->makeDirForResourceOrFail($validator, $dir, $output);
        $this->verifyResourceExistsOrFail($validator, new File($file), $output);

        $content = <<<PHP
<?php

namespace $namespace;

use Brikphp\Validator\Validator;

/**
 * Register a new schema like:
 * 'field' => ['type' => 'string', 'required' => true, ...]
 */
class $validator extends Validator
{
    /**
     * Define the schema for the validator.
     * 
     * @return array The validation schema.
     */
    protected function getSchema(): array
    {
        return [];
    }
}
PHP;

        $this->createResourceOrFail($content, $file, $output);

        return $this->endMessage($output, $validator, $file);
    }
}
