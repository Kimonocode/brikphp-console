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

class ConfigureCommand extends ModuleCommand
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

        // Projet
        $project = new ProjectContainer();
        $project->add('mode','development');
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
        }

        return $this->saveEnvironment($output, $project);
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
        $fileSystem = new FileSystem();
        if (!$fileSystem->create($this->envFile())) {
            $output->writeln("<error>Impossible de créer le fichier {$this->envfile()->getName()}</error>");
            return Command::FAILURE;
        }

        $output->writeln("\n<info>Écriture des variables dans le fichier .env...</info>");

        foreach ($project->data() as $key => $value) {
            $formattedValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $formattedValue = str_contains($formattedValue, ' ') ? "\"{$formattedValue}\"" : $formattedValue;

            if (!$fileSystem->write($this->envFile(), "{$key}={$formattedValue}\n")) {
                $output->writeln("<error>Erreur lors de l'écriture de {$key} dans le fichier .env.</error>");
                return Command::FAILURE;
            }
        }

        $output->writeln("<info>Configuration enregistrée avec succès.</info>");
        $output->writeln("\n<info>Résumé de la configuration :\n</info>");

        foreach ($project->data() as $key => $value) {
            $valueDisplay = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $output->writeln("<comment>{$key}</comment>=<info>{$valueDisplay}</info>");
        }

        $output->writeln("\nVous pouvez maintenant lancez l'application avec la commande <comment>composer dev</comment>\n");
        return Command::SUCCESS;
    }

    /**
     * Demande à l'utilisateur pour supprimer la configuration en cours
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    private function askForDeleteCurrentConfig(InputInterface $input, OutputInterface $output): void
    {
        // Supprimer le fichier si il existe
        $fileSystem = new FileSystem();
        if($this->envFile()->exists()) {
            $this->yesOrNo(
                "Le fichier de configuration .env existe déjà. Supprimer le fichier ?",
                $input,
                $output
            ) ? $fileSystem->delete($this->envFile()) : exit(0);
        }
    }

    /**
     * Demande le nom du projet
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string|null
     */
    private function askProjectName(InputInterface $input, OutputInterface $output): string|null
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
     * @return string|null
     */
    private function askDatabaseName(InputInterface $input, OutputInterface $output): string|null
    {
        return $this->ask('Nom de la base de données (default root) : ', $input, $output);
    }

    /**
     * Demande le nom d'utilisateur pour la db
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string|null
     */
    private function askDatabaseUser(InputInterface $input, OutputInterface $output): string|null
    {
        return $this->ask("Nom d'utilisateur (default root) : ", $input, $output);
    }

    /**
     * Demande le mot de passe pour la db
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string|null
     */
    private function askDatabasePassword(InputInterface $input, OutputInterface $output): string|null
    {
        return $this->ask("Mot de passe de la base de données (optionnel) : ", $input, $output);
    }

    /**
     * Demande l'host pour la db
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string|null
     */
    private function askDatabaseHost(InputInterface $input, OutputInterface $output): string|null
    {
        return $this->ask("Host (default localhost) : ", $input, $output);
    }

    /**
     * Retourne un object File pointant vers le fichier .env
     * @return \Brikphp\FileSystem\FileInterface
     */
    private function envFile(): FileInterface
    {
        return new File(Console::root() . '.env');
    }
}

