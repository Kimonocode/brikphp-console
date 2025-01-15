<?php

namespace Brikphp\Console\Command;

use Brikphp\Console\Console;
use Brikphp\Console\FileSystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigureCommand extends Command
{
    private array $databasesAvailable = ['Mysql', 'PostgreSQL', 'MongoDB'];
    private array $environment = [];

    protected function configure()
    {
        $this->setName('app:configure')
             ->setDescription('Configuration interactive du projet.')
             ->setHelp('Cette commande configure votre projet en demandant les informations nécessaires.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureProject($input, $output);
        $this->saveEnvironment($output);

        $output->writeln("\n<info>Résumé de la configuration :</info>");
        foreach ($this->environment as $key => $value) {
            $valueDisplay = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $output->writeln("  <comment>{$key}</comment>=<info>{$valueDisplay}</info>");
        }

        return Command::SUCCESS;
    }

    private function configureProject(InputInterface $input, OutputInterface $output): void
    {
        $helper = $this->getHelper('question');

        // Nom de l'application
        $appNameQuestion = new Question("\nDonner un nom à votre application : ");
        $appName = $helper->ask($input, $output, $appNameQuestion);

        if (empty($appName)) {
            throw new \RuntimeException('Le nom de l\'application ne peut pas être vide.');
        }

        $this->environment['APP_NAME'] = $appName;
        $this->environment['APP_SECRET'] = bin2hex(random_bytes(32));

        // Base de données
        $useDb = $this->yesOrNo('Utiliser une Base de Données ?', $input, $output);

        if ($useDb) {
            $dbQuestion = new ChoiceQuestion(
                'Quel type de Base de Données voulez-vous utiliser ?',
                $this->databasesAvailable,
                0
            );
            $dbQuestion->setErrorMessage('La Base de Données "%s" est invalide.');
            $db = $helper->ask($input, $output, $dbQuestion);

            $this->environment['DB_CLIENT'] = $db;
            $this->environment['DB_HOST'] = '127.0.0.1';
            $this->environment['DB_USER'] = 'root';

            $dbNameQuestion = new Question("Nom de la base de données : ");
            $dbName = $helper->ask($input, $output, $dbNameQuestion);
            $this->environment['DB_NAME'] = $dbName ?: 'my_database';

            $dbPassQuestion = new Question("Mot de passe de la base de données (optionnel) : ");
            $dbPass = $helper->ask($input, $output, $dbPassQuestion);
            $this->environment['DB_PASS'] = $dbPass ?: '';
        }
    }

    private function saveEnvironment(OutputInterface $output): void
    {
        $filePath = Console::root() . '.env';
        $file = new File($filePath);

        // Supprimer le fichier si il existe
        if($file->exists() && !$file->delete()) {
            throw new \RuntimeException("Le fichier {$file->getName()}, existe mais il a été impossible de le supprimer");
        }

        if (!$file->create()) {
            throw new \RuntimeException("Impossible de créer le fichier {$file->getName()}");
        }

        $output->writeln("\n<info>Écriture des variables dans le fichier .env...</info>");

        foreach ($this->environment as $key => $value) {
            $formattedValue = is_bool($value) ? ($value ? 'true' : 'false') : $value;
            $formattedValue = str_contains($formattedValue, ' ') ? "\"{$formattedValue}\"" : $formattedValue;

            if (!$file->write("{$key}={$formattedValue}\n")) {
                throw new \RuntimeException("Erreur lors de l'écriture de {$key} dans le fichier .env.");
            }
        }

        $output->writeln("<info>Configuration enregistrée avec succès dans {$filePath}.</info>");
    }

    private function yesOrNo(string $question, InputInterface $input, OutputInterface $output): bool
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
}

