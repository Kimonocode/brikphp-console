<?php

namespace Brikphp\Console\Container;

use Brikphp\Console\Console;

class DiContainer extends Container
{
    /**
     * Path to the DI container configuration file.
     * 
     * @var string
     */
    private string $path;

    /**
     * BrikPHP namespace.
     * 
     * @var string
     */
    private string $namespace;

    /**
     * Supported dependency injection methods.
     * 
     * @var string[]
     */
    private array $functionsAvailable = ['get', 'create'];

    /**
     * Constructor initializes the container with the current configuration.
     * @param mixed $filePath
     */
    public function __construct(?string $filePath = null)
    {
        $this->namespace = Console::getNamespace();
        $this->path = $filePath ?? Console::root() . "vendor/{$this->namespace}/src/Core/config.php";
        $this->set($this->open());
    }


    /**
     * Loads the DI container configuration from its file.
     * 
     * @throws \RuntimeException If the file is missing, not writable, or invalid.
     * @return array The loaded configuration.
     */
    public function open(): array
    {
        $path = $this->path;

        if (!file_exists($path)) {
            throw new \RuntimeException("The file {$path} was not found.");
        }

        if (!is_writable($path)) {
            throw new \RuntimeException("The file {$path} is not writable.");
        }

        $container = include $path;

        if (!is_array($container)) {
            throw new \RuntimeException("The configuration file does not return a valid array.");
        }

        return $container;
    }

    /**
     * Writes the current configuration back to the container file.
     * 
     * @throws \RuntimeException If writing to the file fails.
     * @param string|null $file Used for tests
     * @return bool True if the operation was successful.
     */
    public function write(): bool
    {
        $path = $this->path;
        $content = "<?php\n\nreturn [\n";
        foreach ($this->data() as $key => $value) {
            $content .= "    {$this->formatClassReference($key)} => \\DI\\{$this->forceClassReference($value)},\n";
        }
        $content .= "];\n";

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException("Error writing to the configuration file.");
        }
        return true;
    }

    /**
     * Ensures that a class key ends with `::class`.
     * 
     * @param string $key
     * @return string
     */
    public function formatClassReference(string $key): string
    {
        if (substr($key, -7) !== '::class') {
            $key .= '::class';
        }
        return $key;
    }

    /**
     * Enforces the use of `::class` and appends a closing parenthesis if needed.
     * 
     * @param string $key
     * @return string
     */
    public function forceClassReference(string $key): string
    {
        if (!preg_match('/class/', $key)) {
            $key = substr($key, 0, -1) . "::class)";
        }
        return $key;
    }

    /**
     * Removes the `::class` suffix from a key if present.
     * 
     * @param string $key
     * @return string
     */
    public function removeClassReference(string $key): string
    {
        return substr($key, 0, -7);
    }

    /**
     * Checks if the provided injection method is valid.
     * 
     * @param string $function
     * @return bool True if the method is valid, false otherwise.
     */
    public function acceptInjectionFunction(string $function): bool
    {
        return in_array($function, $this->functionsAvailable);
    }

    /**
     * Formats a key with the specified injection function.
     * 
     * @param string $function
     * @param string $key
     * @throws \RuntimeException If the function is not supported.
     * @return string
     */
    public function formatWithInjectionFunction(string $function, string $key): string
    {
        if (!$this->acceptInjectionFunction($function)) {
            throw new \RuntimeException("Invalid function for the dependency injection container.");
        }
        return "{$function}({$key})";
    }
}
