<?php

namespace Vault\Helpers;

/**
 * Class InflectorHelper
 *
 * @package Vault\Helper
 */
class InflectorHelper
{
    /**
     * Returns given word as CamelCased
     * Converts a word like "send_email" to "SendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "WhoSOnline"
     *
     * @see variablize()
     *
     * @param string $word the word to CamelCase
     *
     * @return string
     */
    public static function camelize($word)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Za-z0-9]+/', ' ', $word)));
    }

    /**
     * Converts a CamelCase name into an ID in lowercase.
     * Words in the ID may be concatenated using the specified character (defaults to '-').
     * For example, 'PostTag' will be converted to 'post-tag'.
     *
     * @param string         $name the string to be converted
     * @param string         $separator the character used to concatenate the words in the ID
     * @param boolean|string $strict whether to insert a separator between two consecutive uppercase chars, defaults to false
     *
     * @return string the resulting ID
     */
    public static function camel2id($name, $separator = '-', $strict = false)
    {
        $regex = $strict ? '/[A-Z]/' : '/(?<![A-Z])[A-Z]/';
        if ($separator === '_') {
            return trim(strtolower(preg_replace($regex, '_\0', $name)), '_');
        } else {
            return trim(strtolower(str_replace('_', $separator, preg_replace($regex, $separator . '\0', $name))),
                $separator);
        }
    }

    /**
     * Converts any "CamelCased" into an "underscored_word".
     *
     * @param string $words the word(s) to underscore
     *
     * @return string
     */
    public static function underscore($words)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $words));
    }
}