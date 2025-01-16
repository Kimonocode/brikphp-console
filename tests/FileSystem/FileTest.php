<?php

namespace Brikphp\Console\Tests\FileSystem;

use Brikphp\Console\FileSystem\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase {
    private string $testFilePath;

    protected function setUp(): void
    {
        // Définir le chemin du fichier de test
        $this->testFilePath = __DIR__ . '/testfile.txt';
    }

    protected function tearDown(): void
    {
        // Supprimer le fichier de test s'il existe
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testCreate(): void
    {
        $file = new File($this->testFilePath);
        $this->assertTrue($file->create());
        $this->assertFileExists($this->testFilePath);
    }

    public function testDelete(): void
    {
        $file = new File($this->testFilePath);
        $file->create();

        $this->assertFileExists($this->testFilePath);

        $result = $file->delete();
        $this->assertEquals(0, $result);
        $this->assertFileDoesNotExist($this->testFilePath);
    }

    public function testExists(): void
    {
        $file = new File($this->testFilePath);

        $this->assertFalse($file->exists());
        $file->create();
        $this->assertTrue($file->exists());
    }

    public function testGetNameAndExt(): void
    {
        $file = new File($this->testFilePath);
        $file->create();

        $this->assertEquals('testfile', $file->getName());
        $this->assertEquals('txt', $file->getExt());
    }

    public function testGetSize(): void
    {
        $file = new File($this->testFilePath);
        $file->create();

        $content = "Test content";
        $file->write($content);

        $this->assertEquals(strlen($content), $file->getSize());
    }

    public function testGetDate(): void
    {
        $file = new File($this->testFilePath);
        $file->create();

        $expectedDate = new \DateTime('@' . filemtime($this->testFilePath));
        $this->assertEquals($expectedDate, $file->getDate());
    }
}