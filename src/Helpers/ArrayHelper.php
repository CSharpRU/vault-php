<?php

namespace Vault\Helpers;

use Vault\BaseObject;

/**
 * Class ArrayHelper
 *
 * @package Vault\Helpers
 */
class ArrayHelper
{
    /**
     * @param object|array $object
     * @param bool|true $recursive
     *
     * @return array
     */
    public static function toArray($object, $recursive = true): array
    {
        $array = [];

        if ($object instanceof BaseObject) {
            return $object->toArray($recursive);
        }

        foreach ($object as $key => $value) {
            if ($value instanceof BaseObject) {
                $newValue = $value->toArray($recursive);
            } else {
                $newValue = (is_array($value) || is_object($value)) && $recursive ? self::toArray($value) : $value;
            }

            $array[$key] = $newValue;
        }

        return $array;
    }
}
