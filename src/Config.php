<?php

declare(strict_types=1);

namespace PhpStormGen;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

class Config
{
    public const MODE_IDEA_FOLDER = 'idea-folder';
    public const MODE_SETTINGS_REPOSITORY = 'settings-repository';

    /** @var mixed[] */
    protected $data;

    /**
     * @param mixed[] $config Configuration array
     * @param null|ConfigurationInterface $configDefinition (optional) Custom class to validate the config
     */
    public function __construct(
        array $config
    ) {
        $this->setConfig($config);
    }

    /**
     * @param mixed[] $rawConfig
     */
    private function setConfig(array $rawConfig): void
    {
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new ConfigDefiniton(), [$rawConfig]);
        $this->data = $processedConfig;
    }

    /**
     * Returns all the data in config as associative array
     *
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns value by key. You can supply default value for when the key is missing.
     * Without default value exception is thrown for nonexistent keys.
     *
     * @param string[] $keys
     * @param mixed $default
     * @return mixed
     */
    public function getValue(array $keys, $default = null)
    {
        $config = $this->data;
        $pointer = &$config;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $pointer)) {
                if ($default === null) {
                    throw new InvalidArgumentException(sprintf(
                        'Key "%s" does not exist',
                        implode('.', $keys)
                    ));
                }
                return $default;
            }
            $pointer = &$pointer[$key];
        }
        return $pointer;
    }

    public function getMode()
    {
        return $this->getValue(['mode'], false);
    }
}
