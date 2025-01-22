<?php 

namespace Brikphp\Console\Brik;

use Brikphp\Console\Container\DiContainer;

/**
 * Manages the brik.yml configuration file for the module.
 */
class BrikConfig {

    /**
     * Holds the configuration data.
     * @var array
     */
    private array $config = [];

    /**
     * BrikConfig constructor.
     * Initializes the configuration array.
     * 
     * @param array $config The configuration data.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the key for dependency injection (DI) reference.
     * 
     * @return string The DI injection key.
     */
    public function getDiInjectionKey(): string
    {
        return trim($this->config['di']['injection']['from']);
    }

    /**
     * Returns the value for dependency injection (DI) reference.
     * 
     * @return string The DI injection value.
     */
    public function getDiInjectionValue(): string 
    {
        return trim($this->config['di']['injection']['to']);
    }

    /**
     * Returns the DI injection function used for php-di.
     * 
     * @return string The DI injection function.
     */
    public function getDiInjectionFunction(): string
    {
        return trim($this->config['di']['injection']['function']);
    }

    /**
     * Checks if the module is required in the DI container.
     * 
     * @return bool True if required in the DI container, otherwise false.
     */
    public function isRequiredInDiContainer(): bool
    {
        return $this->config['di']['required'];
    }

    /**
     * Injects the module dependencies into the php-di container.
     * 
     * @throws \RuntimeException If the injection cannot be completed.
     * @return bool True if the injection was successful.
     */
    public function injectInDiContainer(): bool
    {
        $container = new DiContainer();
        
        // Retrieve DI function and references.
        $function = $this->getDiInjectionFunction();
        $from = $container->formatClassReference($this->getDiInjectionKey());
        $to = $container->formatClassReference($this->getDiInjectionValue());

        // Check if the class is already initialized in the container.
        foreach ($container->data() as $key => $value) {
            if ($key === $container->removeClassReference($from)) {
                throw new \RuntimeException("The dependency is already initialized in the container.");
            }
        }

        // Validate the DI function.
        if (!$container->acceptInjectionFunction($function)) {
            throw new \RuntimeException("The DI method '{$function}' is invalid.");
        }

        // Add the new configuration to the container.
        $container->add($from, $container->formatWithInjectionFunction($function, $to));

        // Write the new configuration to the container.
        return $container->write();
    }
}
