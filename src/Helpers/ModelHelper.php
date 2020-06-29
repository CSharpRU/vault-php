<?php

namespace Vault\Helpers;

/**
 * Class Model
 *
 * @package Vault\Helper
 */
class ModelHelper
{
    /**
     * @param array $data
     * @param bool $recursive
     *
     * @return array
     */
    public static function camelize(array $data, $recursive = true): array
    {
        $return = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && $recursive) {
                $value = self::camelize($value, $recursive);
            }

            $return[self::camelizeString($key)] = $value;
        }

        return $return;
    }

    private static function camelizeString(string $data): string
    {
        $camelizedString = str_replace([' ', '_', '-'], '', ucwords($data, ' _-'));

        return lcfirst($camelizedString);
    }
}
