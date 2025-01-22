<?php

namespace Brikphp\Console\Command\Maker;

use Brikphp\FileSystem\File;
use Brikphp\Console\Command\Base\MakerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeControllerCommand extends MakerCommand {

    protected function configure()
    {
        $this->setConfiguration('controller', 'src/Http/Controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $controller = $this->makeArgumentName($input, 'Controller');  
        $namespace  = $this->makeNamespace('Http\\Controller');
        $dir        = $this->makeDirPath("Http/Controller");
        $file       = $this->makeFilePath("Http/Controller/$controller");

        $this->makeDirForResourceOrFail( $controller, $dir, $output);
        $this->verifyResourceExistsOrFail($controller, new File($file), $output);

        $content = <<<PHP
<?php

namespace $namespace;

use Brikphp\Http\Controller\Controller;

class $controller extends Controller {

}
PHP;


        $this->createResourceOrfail($content, $file, $output);
        
        return $this->endMessage($output, $controller, $file);
    }
}