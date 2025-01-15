<?php 

namespace Brikphp\Console;

use Brikphp\Console\Command\ConfigureCommand;
use Symfony\Component\Console\Application;

class Console extends Application {

    private string $name;

    private string $version;

    public function __construct(string $name, string $version)
    {
        parent::__construct($name, $version);
        $this->add(new ConfigureCommand());
    }

    public static function root(): string 
    {
        return getcwd() . DIRECTORY_SEPARATOR;
    }

}