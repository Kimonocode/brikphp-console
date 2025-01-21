<?php

namespace Brikphp\Console\Server;

use React\EventLoop\LoopInterface;

/**
 * Watcher class monitors a directory for changes and notifies a WebSocket server to reload clients.
 * 
 * This class is useful for development environments where live reloading is needed upon file changes.
 */
class Watcher {

    /**
     * @var WebSocketServer The WebSocket server instance for broadcasting reload notifications.
     */
    private WebSocketServer $webSocketServer;

    /**
     * @var string The directory to watch for file changes.
     */
    private string $directory;

    /**
     * @var array Array storing the last modified timestamps of watched files.
     */
    private array $fileTimestamps = [];

    /**
     * Loop interface for use the loop inside the watcher
     * @var LoopInterface
     */
    private LoopInterface $loop;

    private bool $reloadInProgress = false;

    /**
     * Constructor for the Watcher class.
     *
     * @param WebSocketServer $webSocketServer The WebSocket server to notify on file changes.
     * @param string|null $directory The directory to monitor. Defaults to the current directory.
     * @param LoopInterface|null $loop
     */
    public function __construct
    (
        WebSocketServer $webSocketServer, 
        ?string $directory = __DIR__, 
        ?LoopInterface $loop = null
    ) 
    {
        $this->webSocketServer = $webSocketServer;
        $this->directory = $directory;
        $this->loop = $loop;
    }

    /**
     * Starts watching the directory for changes and triggers reload notifications.
     *
     * Uses React's event loop to periodically check for file modifications.
     */
    public function watch(): void
    {
        $webSocketServer = $this->webSocketServer;
        $fileTimestamps = &$this->fileTimestamps;
        $directory = rtrim($this->directory, '/');

        $this->loop->addPeriodicTimer(2, function () use ($webSocketServer, $directory, &$fileTimestamps) {
            if ($this->reloadInProgress) {
                return; // Ignore les cycles si un rechargement est déjà en cours
            }

            $files = glob("$directory/**/*.{php,html,css,js}", GLOB_BRACE);

            if ($files === false) {
                echo "Error reading directory: $directory\n";
                return;
            }

            foreach ($files as $file) {
                $modTime = filemtime($file);

                if ($modTime === false) {
                    echo "Error reading file modification time: $file\n";
                    continue;
                }

                if (!isset($fileTimestamps[$file]) || $modTime > $fileTimestamps[$file]) {
                    $fileTimestamps[$file] = $modTime;
                    $this->reloadInProgress = true; // Active le verrou
                    $webSocketServer->reloadClients();
                    $this->reloadInProgress = false; // Désactive le verrou
                    break; // Quittez la boucle après la première modification détectée
                }
            }
        });
    }
}
