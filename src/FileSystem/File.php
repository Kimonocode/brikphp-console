<?php

namespace Brikphp\Console\FileSystem;

use DateTime;

class File implements FileInterface
{   
    /**
     * Chemin du fichier
     * @var string
     */
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @inheritDoc
     */
    public function create(): bool
    {
        return touch($this->filePath);
    }
    
    /**
     * @inheritDoc
     */
    public function read(): string
    {
        return file_exists($this->filePath) ? file_get_contents($this->filePath) : '';
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function delete(): int
    {
        return unlink($this->filePath) ? 0 : 1;
    }
    
    /**
     * @inheritDoc
     */
    public function exists(): bool
    {
        return file_exists($this->filePath);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return pathinfo($this->filePath, PATHINFO_FILENAME);
    }

    /**
     * @inheritDoc
     */
    public function getExt(): string
    {
        return pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    /**
     * @inheritDoc
     */
    public function getSize(): int
    {
        return file_exists($this->filePath) ? filesize($this->filePath) : 0;
    }

    /**
     * @inheritDoc
     */
    public function getDate(): DateTime
    {
        return new DateTime('@' . filemtime($this->filePath));
    }
}
