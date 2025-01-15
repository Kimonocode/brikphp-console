<?php

namespace Brikphp\Console\FileSystem;

use DateTime;

class File implements FileInterface
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function create(): bool
    {
        return touch($this->filePath);
    }

    public function read(): string
    {
        return file_exists($this->filePath) ? file_get_contents($this->filePath) : '';
    }

    public function write(string $content): int
    {
        $fileHandle = fopen($this->filePath, 'a'); // Ouverture en mode append
        if ($fileHandle === false) {
            return false;
        }

        $result = fwrite($fileHandle, $content);
        fclose($fileHandle);

        return $result !== false;
    }

    public function delete(): int
    {
        return unlink($this->filePath) ? 0 : 1;
    }
    
    public function exists(): bool
    {
        return file_exists($this->filePath);
    }

    public function getName(): string
    {
        return pathinfo($this->filePath, PATHINFO_FILENAME);
    }

    public function getExt(): string
    {
        return pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    public function getSize(): int
    {
        return file_exists($this->filePath) ? filesize($this->filePath) : 0;
    }

    public function getDate(): DateTime
    {
        return new DateTime('@' . filemtime($this->filePath));
    }
}
