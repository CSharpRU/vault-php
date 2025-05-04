<?php

namespace Vault\Helpers;

use Vault\Exceptions\RuntimeException;

/**
 * Class ModelHelper
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

    /**
     * @param array $data
     * @param bool $recursive
     *
     * @return array
     * 
     * @throws RuntimeException
     */
    public static function snakelize(array $data, $recursive = true): array
    {
        $return = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && $recursive) {
                $value = self::snakelize($value, $recursive);
            }

            $return[self::snakelizeString($key)] = $value;
        }

        return $return;
    }

    private static function snakelizeString(string $data): string
    {
        $snakelizedString = preg_replace('~(?<=\\w)([A-Z])~u', '_$1', $data);

        if ($snakelizedString === null) {
            throw new RuntimeException(sprintf(
                'preg_replace returned null when trying to snakelize value "%s"',
                $data
            ));
        }

        return mb_strtolower($snakelizedString);
    }
}
