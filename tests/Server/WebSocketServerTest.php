<?php

namespace Brikphp\Console\Tests\Server;

use PHPUnit\Framework\TestCase;
use Brikphp\Console\Server\WebSocketServer;
use Ratchet\ConnectionInterface;

class WebSocketServerTest extends TestCase
{
    private WebSocketServer $server;

    protected function setUp(): void
    {
        $this->server = new WebSocketServer();
    }

    public function testOnOpenAddsClientToList(): void
    {
        $mockConnection = $this->createMock(ConnectionInterface::class);
        $mockConnectionId = spl_object_hash($mockConnection);

        $this->server->onOpen($mockConnection);

        $reflection = new \ReflectionClass($this->server);
        $clientsProperty = $reflection->getProperty('clients');
        $clientsProperty->setAccessible(true);
        $clients = $clientsProperty->getValue($this->server);

        $this->assertArrayHasKey($mockConnectionId, $clients);
        $this->assertSame($mockConnection, $clients[$mockConnectionId]);
    }

    public function testOnMessageDoesNotThrowError(): void
    {
        $mockConnection = $this->createMock(ConnectionInterface::class);

        try {
            $this->server->onMessage($mockConnection, "test message");
            $this->assertTrue(true); // Test passed, no exception thrown.
        } catch (\Throwable $e) {
            $this->fail("onMessage threw an exception: " . $e->getMessage());
        }
    }

    public function testOnCloseRemovesClientFromList(): void
    {
        $mockConnection = $this->createMock(ConnectionInterface::class);
        $mockConnectionId = spl_object_hash($mockConnection);

        $this->server->onOpen($mockConnection);
        $this->server->onClose($mockConnection);

        $reflection = new \ReflectionClass($this->server);
        $clientsProperty = $reflection->getProperty('clients');
        $clientsProperty->setAccessible(true);
        $clients = $clientsProperty->getValue($this->server);

        $this->assertArrayNotHasKey($mockConnectionId, $clients);
    }

    public function testBroadcastSendsMessageToAllClients(): void
    {
        $mockConnection1 = $this->createMock(ConnectionInterface::class);
        $mockConnection2 = $this->createMock(ConnectionInterface::class);

        $mockConnection1->expects($this->once())
            ->method('send')
            ->with('test message');

        $mockConnection2->expects($this->once())
            ->method('send')
            ->with('test message');

        $this->server->onOpen($mockConnection1);
        $this->server->onOpen($mockConnection2);

        $reflection = new \ReflectionClass($this->server);
        $broadcastMethod = $reflection->getMethod('broadcast');
        $broadcastMethod->setAccessible(true);

        $broadcastMethod->invoke($this->server, 'test message');
    }

    public function testReloadClientsBroadcastsReloadMessage(): void
    {
        $mockConnection = $this->createMock(ConnectionInterface::class);

        $mockConnection->expects($this->once())
            ->method('send')
            ->with('reload');

        $this->server->onOpen($mockConnection);

        $this->server->reloadClients();
    }

    public function testOnErrorClosesConnection(): void
    {
        $mockConnection = $this->createMock(ConnectionInterface::class);

        $mockConnection->expects($this->once())
            ->method('close');

        $this->server->onError($mockConnection, new \Exception("Test exception"));
    }
}
