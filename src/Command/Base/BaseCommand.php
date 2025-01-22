<?php

namespace Brikphp\Console\Command\Base;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Base command class providing common utility methods for user interaction.
 */
class BaseCommand extends Command {

    /**
     * Asks a generic Yes or No question.
     *
     * @param string $question The question to ask the user.
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return bool True if the user answered 'y', false otherwise.
     */
    protected function yesOrNo(string $question, InputInterface $input, OutputInterface $output): bool
    {
        $helper = $this->getHelper('question');
        while (true) {
            $response = strtolower($helper->ask($input, $output, new Question("{$question} [y/N]: ")));
            if ($response === 'y') {
                return true;
            }
            if ($response === 'n' || $response === '') {
                return false;
            }
            $output->writeln('<error>Invalid response. Please answer with "y" (yes) or "N" (no).</error>');
        }
    }

    /**
     * Asks a generic question and returns the user's response.
     *
     * @param string $question The question to ask the user.
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return string|null The user's response or null if no response was provided.
     */
    protected function ask(string $question, InputInterface $input, OutputInterface $output): string|null
    {
        $helper = $this->getHelper('question');
        $question = new Question($question);
        return $helper->ask($input, $output, $question);
    }

    protected function success(OutputInterface $output, string $message, ?string $prefix = null, ?string $suffix = null): int
    {
        $prefix ??= '';
        $suffix ??= '';
        $output->writeLn("{$prefix}✔️  {$message} {$suffix}");
        return Command::SUCCESS;
    }   
}
