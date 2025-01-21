<?php

namespace Brikphp\Console\Tests\Server;

use PHPUnit\Framework\TestCase;
use Brikphp\Console\Server\Watcher;
use Brikphp\Console\Server\WebSocketServer;
use React\EventLoop\LoopInterface;

class WatcherTest extends TestCase
{
    private $webSocketServer;
    private $loop;
    private $watcher;

    protected function setUp(): void
    {
        // Créez un mock de WebSocketServer
        $this->webSocketServer = $this->createMock(WebSocketServer::class);

        // Créez un mock de LoopInterface
        $this->loop = $this->createMock(LoopInterface::class);

        // Initialisez Watcher avec les mocks
        $this->watcher = new Watcher($this->webSocketServer, __DIR__, $this->loop);
    }

    public function testWatchTriggersReloadOnFileChange(): void
    {
        $testFile = __DIR__ . '/test-directory/test.php';

        if (!file_exists(__DIR__ . '/test-directory')) {
            mkdir(__DIR__ . '/test-directory', 0777, true);
        }
        file_put_contents($testFile, '<?php echo "Hello"; ?>');

        // Configurez une attente sur reloadClients
        $this->webSocketServer->expects($this->once())
            ->method('reloadClients');

        // Simulez l'ajout d'un callback à la boucle
        $this->loop->expects($this->once())
            ->method('addPeriodicTimer')
            ->willReturnCallback(function ($interval, $callback) use ($testFile) {
                // Simulez une modification du fichier
                touch($testFile, time() + 1);

                // Appelez le callback
                $callback();
            });

        // Lancez le watcher
        $this->watcher->watch();

        // Nettoyez
        unlink($testFile);
        rmdir(__DIR__ . '/test-directory');
    }
}


