<?php

namespace Brikphp\Console\Command;

use Brikphp\Console\Console;
use Brikphp\Console\FileSystem\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigureModuleCommand extends Command
{
    /**
     * Namespace du package par défaut
     * @var string
     */
    private string $namespace = 'kimonocode/brikphp';

    /**
     * Modules valides
     * @var string[]
     */
    private array $modulesAvailable = [
        'validator',
        'logger',
        'database',
        'mailer',
        'session',
        'renderer',
    ];

    /**
     * Configuration de la commande
     * 
     * @return void
     */
    protected function configure()
    {
        $this->setName('brik:configure')
            ->setDescription("Configure un nouveau module ajouté à votre application.")
            ->setHelp("Cette commande configure un nouveau module ajouté à votre application.")
            ->addArgument('module', InputArgument::REQUIRED, 'Nom du module.');
    }

    /**
     * Main Function
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \RuntimeException
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // vérifie si le module se trouve dans la liste de module valide
        $module = strtolower($input->getArgument('module'));
        if (!in_array($module, $this->modulesAvailable)) {
            $output->writeln("\n<error>ERROR</error> Le module '{$module}' est invalide.\n");
            return Command::FAILURE;
        }

        // vérifie si le module est correctement installé.
        $brik = new File($this->pathToModule($module));
        if (!$brik->exists()) {
            $output->writeln("\n<error>ERROR</error> Le module '{$module}' n'est pas installé.\n");
            return Command::FAILURE;
        }

        // vérifie la configuration
        $brikConfig = Yaml::parseFile($this->pathToModule($module));

        // Vérifier que les clés nécessaires existent
        if (!isset($brikConfig['di']['required']) || !isset($brikConfig['di']['method']) || !isset($brikConfig['di']['from']) || !isset($brikConfig['di']['to'])) {
            $output->writeln("<error>ERROR</error> La configuration DI est invalide.");
            return Command::FAILURE;
        }

        $brikIsRequiredInDIContainer = $brikConfig['di']['required'];

        $output->writeln("\nConfiguration du module {$module} ...\n");

        // Ajoute le module dans le container d'injections de dépendance si besoin
        if ($brikIsRequiredInDIContainer) {
            if (!$this->addModuleInDiContainer($brikConfig)) {
                throw new \RuntimeException("Impossible d'initialiser le module dans le container.");
            }
        }

        $output->writeln("\n<info>Le module {$module} à été initialisé.</info>\n");
        return Command::SUCCESS;
    }

    /**
     * Retourne le chemin du fichier brik.yml du module
     * 
     * @param string $module
     * @return string
     */
    private function pathToModule(string $module): string
    {
        return Console::root() . "vendor/{$this->namespace}-{$module}/brik.yml";
    }

    /**
     * Ajoute le module dans le container d'injections de dépendances
     * 
     * @param array $brikConfig
     * @throws \RuntimeException
     * @return bool True si le module a été injecté
     */
    private function addModuleInDiContainer(array $brikConfig): bool
    {
        // Validation de la configuration DI
        if (empty($brikConfig['di']) || !isset($brikConfig['di']['method'], $brikConfig['di']['from'], $brikConfig['di']['to'])) {
            throw new \InvalidArgumentException("Configuration DI invalide dans le fichier YAML.");
        }

        $diKey = $brikConfig['di'];
        $diMethod = trim($diKey['method']);
        $diFrom = trim($diKey['from']);
        $diTo = trim($diKey['to']);

        // Vérification des valeurs pour la méthode DI
        if (!in_array($diMethod, ['get', 'create'])) {
            throw new \RuntimeException("La méthode DI '{$diMethod}' n'est pas valide. Utilisez 'get' ou 'create'.");
        }

        // Chemin du fichier de configuration du conteneur
        $containerPath = Console::root() . "vendor/{$this->namespace}/src/Core/config.php";

        // Vérifier si le fichier existe et est accessible en écriture
        if (!file_exists($containerPath)) {
            throw new \RuntimeException("Le fichier {$containerPath} est introuvable.");
        }

        if (!is_writable($containerPath)) {
            throw new \RuntimeException("Le fichier {$containerPath} n'est pas accessible en écriture.");
        }

        // Charger la configuration actuelle du conteneur
        $containerConfig = include $containerPath;

        // Vérifier si le fichier de configuration retourne un tableau
        if (!is_array($containerConfig)) {
            throw new \RuntimeException("Le fichier de configuration ne retourne pas un tableau valide.");
        }

        // Vérifier si la clé existe déjà
        if (array_key_exists($diFrom, $containerConfig)) {
            throw new \RuntimeException("La clé {$diFrom} existe déjà dans le container.");
        }

        if (substr($diFrom, -7) !== '::class') {
            $diFrom .= '::class';
        }
        
        if (substr($diTo, -7) !== '::class') {
            $diTo .= '::class';
        }

        // Construire l'injection DI avec la méthode appropriée
        $injectionFunction = ($diMethod === 'get') ? "get({$diTo})" : "create({$diTo})";

        // Ajouter la nouvelle configuration au tableau
        $containerConfig[$diFrom] = $injectionFunction;

        $configContent = "<?php\n\nreturn [\n";
        foreach ($containerConfig as $key => $value) {

            $configContent .= "    {$key} => \DI\{$value},\n";
        }
        $configContent .= "];\n";

        // Écrire la nouvelle configuration dans le fichier
        if (file_put_contents($containerPath, $configContent) === false) {
            throw new \RuntimeException("Erreur lors de l'écriture dans le fichier de configuration.");
        }

        return true;
    }

}
