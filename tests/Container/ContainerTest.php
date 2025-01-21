<?php

namespace Brikphp\Console\Tests\Container;

use Brikphp\Console\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    /**
     * Test if items can be added and retrieved from the container.
     */
    public function testAddAndGet(): void
    {
        $this->container->add('logger', 'LoggerInstance');
        $this->assertTrue($this->container->has('logger'));
        $this->assertSame('LoggerInstance', $this->container->get('logger'));
    }

    /**
     * Test retrieving a non-existent key.
     */
    public function testGetNonExistentKey(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $this->container->get('non_existent_key');
    }

    /**
     * Test checking for key existence in the container.
     */
    public function testHas(): void
    {
        $this->assertFalse($this->container->has('database'));
        $this->container->add('database', 'DatabaseInstance');
        $this->assertTrue($this->container->has('database'));
    }

    /**
     * Test replacing the container data.
     */
    public function testSet(): void
    {
        $this->container->add('key1', 'value1');
        $this->container->add('key2', 'value2');

        $this->container->set(['key3' => 'value3', 'key4' => 'value4']);
        
        $this->assertFalse($this->container->has('key1'));
        $this->assertTrue($this->container->has('key3'));
        $this->assertSame('value3', $this->container->get('key3'));
    }

    /**
     * Test retrieving all data from the container.
     */
    public function testData(): void
    {
        $this->container->add('logger', 'LoggerInstance');
        $this->container->add('database', 'DatabaseInstance');
        
        $expectedData = [
            'logger' => 'LoggerInstance',
            'database' => 'DatabaseInstance',
        ];
        
        $this->assertSame($expectedData, $this->container->data());
    }

    /**
     * Test overwriting an existing key.
     */
    public function testOverwriteKey(): void
    {
        $this->container->add('logger', 'LoggerInstance');
        $this->container->add('logger', 'NewLoggerInstance');
        
        $this->assertSame('NewLoggerInstance', $this->container->get('logger'));
    }

    /**
     * Test adding and retrieving a complex value (object).
     */
    public function testAddObject(): void
    {
        $object = new \stdClass();
        $object->property = 'value';

        $this->container->add('object', $object);

        $retrievedObject = $this->container->get('object');
        $this->assertSame($object, $retrievedObject);
        $this->assertEquals('value', $retrievedObject->property);
    }
}
