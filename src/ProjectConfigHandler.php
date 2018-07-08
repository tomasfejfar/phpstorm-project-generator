<?php

declare(strict_types=1);

namespace PhpStormGen;

use function file_get_contents;
use function file_put_contents;
use function getcwd;
use function json_decode;

class ProjectConfigHandler
{
    public function load()
    {
        $data = json_decode(file_get_contents($this->getConfigPath()), true);
        return new Config($data);
    }

    public function save(Config $config)
    {
        file_put_contents($this->getConfigPath(), json_encode($config->getData()));
    }

    /**
     * @return string
     */
    protected function getConfigPath(): string
    {
        return getcwd() . '/.ide-tpl/config.json';
    }
}
