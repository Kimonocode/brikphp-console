<?php

namespace Brikphp\Console\Command\Base;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends Command {

    /**
     * Pose Une question générique Oui ou Non
     *
     * @param string $question
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return bool
     */
    protected function yesOrNo(string $question, InputInterface $input, OutputInterface $output): bool
    {
        $helper = $this->getHelper('question');
        while (true) {
            $response = strtolower($helper->ask($input, $output, new Question("{$question} [y/N] : ")));
            if ($response === 'y') {
                return true;
            }
            if ($response === 'n' || $response === '') {
                return false;
            }
            $output->writeln('<error>Réponse invalide. Veuillez répondre par "y" (oui) ou "N" (non).</error>');
        }
    }

    /**
     * Pose une question générique et retourne la réponse
     * 
     * @param string $question
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string|null
     */
    protected function ask(string $question, InputInterface $input, OutputInterface $output): string|null
    {
        $helper = $this->getHelper('question');
        $question = new Question($question);
        return $helper->ask($input, $output, $question);
    }
}