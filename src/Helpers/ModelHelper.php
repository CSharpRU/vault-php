<?php

namespace Vault\Helpers;

use Doctrine\Common\Inflector\Inflector;

/**
 * Class Model
 *
 * @package Vault\Helper
 */
class ModelHelper
{
    /**
     * @param array $data
     * @param bool  $recursive
     *
     * @return array
     */
    public static function camelize(array $data, $recursive = true)
    {
        $return = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && $recursive) {
                $value = self::camelize($value, $recursive);
            }

            $return[Inflector::camelize($key)] = $value;
        }

        return $return;
    }
}