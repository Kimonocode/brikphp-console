<?php

namespace Brikphp\Console\Command\Project;

use Brikphp\Console\Command\Base\BaseCommand;
use Brikphp\Console\Console;
use Brikphp\Console\Container\ProjectContainer;
use Brikphp\Console\FileSystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigureCommand extends BaseCommand
{   
    /**
     * Bases de données compatible
     * @var string[]
     */
    private array $databasesAvailable = ['Mysql', 'PostgreSQL', 'MongoDB'];

    /**
     * Configuration générale de la commande
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('app:configure')
            ->setDescription('Configuration interactive du projet.')
            ->setHelp('Cette commande configure votre projet en demandant les informations nécessaires.');
    }

    /**
     * Exécution générale de la commande
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->askForDeleteCurrentConfig($input, $output);

        $project = new ProjectContainer();
        $project->add('APP_NAME', $this->askProjectName($input, $output) ?: 'Demo');
        $project->add('APP_SECRET', bin2hex(random_bytes(32)));

        // Base de données
        $useDb = $this->yesOrNo('Utiliser une Base de Données ?', $input, $output);

        if ($useDb) {
            $project->add('DB_CLIENT', $this->askDatabaseClient($input, $output) ?: 'Mysql');
            $project->add('DB_HOST', $this->askDatabaseHost($input, $output) ?: '127.0.0.1');
            $project->add('DB_USER', $this->askDatabaseUser($input, $output) ?: 'root');
            $project->add('DB_NAME', $this->askDatabaseName($input, $output) ?: 'demo');
            $project->add('DB_PASS', $this->askDatabasePassword($input, $output) ?: '');

            $this->saveEnvironment($output, $project);
        }

        return Command::SUCCESS;
    }

    /**
     * Sauvegarde le fichier .env avec les différentes variables du project
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param ProjectContainer $project
     * @throws \RuntimeException
     * @return int
     */
    private function saveEnvironment(OutputInterface $output, ProjectContainer $project): int 
    {
        $file = $this->getEnv();
        if (!$file->create()) {
            $output->writeln("<error>Impossible de créer le fichier {$file->getName()}</error>");
            return Command::FAILURE;
        }

        $output->writeln("\n<info>Écriture des variables dans le fichier .env...</info>");

        foreach ($project->data() as $key => $value) {
            $formattedValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $formattedValue = str_contains($formattedValue, ' ') ? "\"{$formattedValue}\"" : $formattedValue;

            if (!$file->write("{$key}={$formattedValue}\n")) {
                $output->writeln("<error>Erreur lors de l'écriture de {$key} dans le fichier .env.</error>");
                return Command::FAILURE;
            }
        }

        $output->writeln("<info>Configuration enregistrée avec succès.</info>");
        $output->writeln("\n<info>Résumé de la configuration :\n</info>");

        foreach ($project->data() as $key => $value) {
            $valueDisplay = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $output->writeln("<comment>{$key}</comment>=<info>{$valueDisplay}</info>");
            $output->writeln("");
        }

        return Command::SUCCESS;
    }

    /**
     * Demande à l'utilisateur pour supprimer la configuration en cours
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    private function askForDeleteCurrentConfig(InputInterface $input, OutputInterface $output)
    {
        $file = $this->getEnv();

        // Supprimer le fichier si il existe
        if($file->exists()) {
            $this->yesOrNo(
                "Le fichier de configuration .env existe déjà. Supprimer le fichier ?",
                $input,
                $output
            ) ? $file->delete() : exit(0);
        }
    }

    /**
     * Demande le nom du projet
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    private function askProjectName(InputInterface $input, OutputInterface $output): string
    {
        return $this->ask("\nDonner un nom à votre projet (Demo) : ", $input, $output);
    }

    /**
     * Demande le type de base de données à utiliser
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    private function askDatabaseClient(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Quel type de Base de Données voulez-vous utiliser ? (default Mysql)',
            $this->databasesAvailable,
            0
        );
        $question->setErrorMessage('La Base de Données "%s" est invalide.');
        return $helper->ask($input, $output, $question);
    }

    /**
     * Demande le nom de la base de données
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    private function askDatabaseName(InputInterface $input, OutputInterface $output): string
    {
        return $this->ask('Nom de la base de données (default root) : ', $input, $output);
    }

    /**
     * Demande le nom d'utilisateur pour la db
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    private function askDatabaseUser(InputInterface $input, OutputInterface $output): string
    {
        return $this->ask("Nom d'utilisateur (default root) : ", $input, $output);
    }

    /**
     * Demande le mot de passe pour la db
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    private function askDatabasePassword(InputInterface $input, OutputInterface $output): string
    {
        return $this->ask("Mot de passe de la base de données (optionnel) : ", $input, $output);
    }

    /**
     * Demande l'host pour la db
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     */
    private function askDatabaseHost(InputInterface $input, OutputInterface $output): string
    {
        return $this->ask("Host (default localhost) : ", $input, $output);
    }

    /**
     * Accèes au fichier .env
     *
     * @return File
     */
    private function getEnv()
    {
        return new File(Console::root() . '.env');
    }
}
