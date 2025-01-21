<?php 

namespace Brikphp\Console\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * WebSocketServer class implements a simple WebSocket server using Ratchet.
 * 
 * This class handles WebSocket connections, broadcasts messages to clients, and manages connection events.
 */
class WebSocketServer implements MessageComponentInterface {

    /**
     * @var ConnectionInterface[] List of active client connections
     */
    private array $clients = [];

    /**
     * Triggered when a new client establishes a WebSocket connection.
     *
     * @param ConnectionInterface $conn The connection instance for the new client.
     */
    public function onOpen(ConnectionInterface $conn): void {
        $this->clients[spl_object_hash($conn)] = $conn;
    }

    /**
     * Triggered when a message is received from a client.
     *
     * @param ConnectionInterface $from The client sending the message.
     * @param string $msg The message content.
     */
    public function onMessage(ConnectionInterface $from, $msg): void {
        // Process the received message (extend this logic as needed).
    }

    /**
     * Triggered when a client connection is closed.
     *
     * @param ConnectionInterface $conn The connection instance for the closing client.
     */
    public function onClose(ConnectionInterface $conn): void {
        $clientId = spl_object_hash($conn);
        unset($this->clients[$clientId]);
    }

    /**
     * Triggered when an error occurs on a client connection.
     *
     * @param ConnectionInterface $conn The connection instance where the error occurred.
     * @param \Exception $e The exception thrown during the error.
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void {
        // Log the error or take further action if necessary.
        $conn->close();
    }

    /**
     * Broadcasts a reload command to all connected clients.
     */
    public function reloadClients(): void {
        $this->broadcast('reload');
    }

    /**
     * Sends a message to all connected clients.
     *
     * @param string $message The message to send.
     */
    private function broadcast(string $message): void {
        foreach ($this->clients as $client) {
            try {
                $client->send($message);
            } catch (\Exception $e) {
                // Handle potential errors during message sending.
                $this->onError($client, $e);
            }
        }
    }
}
