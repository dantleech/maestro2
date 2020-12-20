<?php

namespace Maestro\Core\Util;

class PermissionUtil
{
    public static function formatOctal(int $permission): string
    {
        return substr(sprintf('%o', $permission), -4);
    }
}
