<?php

namespace Brikphp\Console\Command;

use Brikphp\Console\Console;
use Brikphp\Console\Server\Watcher;
use Brikphp\Console\Server\WebSocketServer;
use Brikphp\Core\App;
use Brikphp\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Socket\SocketServer;
use Ratchet\Http\HttpServer as RatchetHttpServer;
use React\Socket\TcpServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to start the development server with HTTP and WebSocket capabilities.
 */
class StartServerCommand extends Command {

    private array $clients = [];

    /**
     * Configures the command's name, description, and help.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('app:start')
            ->setDescription('Starts the development server.')
            ->setHelp('This command starts a development server with HOT-RELOAD functionality.')
            ->addOption('watch');
    }

    /**
     * Executes the command to start the development server.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int Exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("🚀 Welcome to Development Server");

        $app = new App();
        $webSocketServer = new WebSocketServer();

        // HTTP Server
        $httpServer = new HttpServer(function (ServerRequestInterface $request) use ($app) {
            try {
                return $app->run($request);
            } catch (\Throwable $e) {
                return new Response(
                    500, 
                    ["Content-Type: text/plain"], 
                    "Whoops ! Internale Server Error: {$e->getMessage()}"
                );
            }
        });

        $httpSocket = new SocketServer('0.0.0.0:8000');
        $httpServer->listen($httpSocket);

        // WebSocket Server
        $wsServer = new WsServer($webSocketServer);
        $wsHttpServer = new RatchetHttpServer($wsServer);
        $wsSocket = new TcpServer('0.0.0.0:9000', Loop::get());
        
        $ioServer = new IoServer($wsHttpServer, $wsSocket, Loop::get());

        // Watch for HOT-RELOAD
        if ($input->getOption('watch')) {
            $watcher = new Watcher($webSocketServer, Console::root(), Loop::get());
            $watcher->watch();
            $output->writeLn("👀 Files watched\n");
        }

        $output->writeLn("🌐✔️  HTTP Server ready on: http://localhost:8000");
        $output->writeLn("🌐✔️  Web Socket Server ready on: ws://localhost:9000");
        $output->writeln("\nPress CTRL+C to stop.");

        Loop::run();

        return Command::SUCCESS;
    }
}