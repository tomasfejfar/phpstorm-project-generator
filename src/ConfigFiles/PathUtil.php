<?php

declare(strict_types=1);

namespace PhpStormGen\ConfigFiles;

class PathUtil
{
    public static function sanitizePath(string $path): string
    {
        return rtrim(strtr($path, '\\/', '/'), '/');
    }

    public static function pathFromSegments(array $segments): string
    {
        return implode('/', $segments);
    }
}
