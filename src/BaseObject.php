<?php

namespace Vault;

use RuntimeException;
use Vault\Helpers\ArrayHelper;

/**
 * Class Object
 *
 * @package Vault
 */
class BaseObject
{
    /**
     * Object constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                if (!$this->canSetProperty($name)) {
                    continue;
                }

                $this->$name = $value;
            }
        }
    }

    /**
     * @param string $name
     * @param bool $checkVars
     *
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true): bool
    {
        return method_exists($this, 'set' . $name) || ($checkVars && property_exists($this, $name));
    }

    /**
     * @param $name
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        if (method_exists($this, 'set' . $name)) {
            throw new RuntimeException('Getting write-only property: ' . get_class($this) . '::' . $name);
        }

        throw new RuntimeException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @throws RuntimeException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;

        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new RuntimeException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new RuntimeException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;

        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @throws RuntimeException
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;

        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new RuntimeException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * @param string $name
     * @param array $params
     *
     * @throws RuntimeException
     */
    public function __call($name, $params)
    {
        throw new RuntimeException('Unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * @param string $name
     * @param bool $checkVars
     *
     * @return bool
     */
    public function hasProperty($name, $checkVars = true): bool
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    /**
     * @param string $name
     * @param bool $checkVars
     *
     * @return bool
     */
    public function canGetProperty($name, $checkVars = true): bool
    {
        return method_exists($this, 'get' . $name) || ($checkVars && property_exists($this, $name));
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasMethod($name): bool
    {
        return method_exists($this, $name);
    }

    /**
     * @param bool $recursive
     *
     * @return array
     */
    public function toArray($recursive = true): array
    {
        $data = [];

        foreach ($this->getFields() as $field => $definition) {
            $data[$field] = is_string($definition) ? $this->$definition : $definition($this, $field);
        }

        return $recursive ? ArrayHelper::toArray($data, $recursive) : $data;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        $result = [];

        $fields = array_keys(get_object_vars($this));
        $fields = array_combine($fields, $fields);

        foreach ($fields as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }

            $result[$field] = $definition;
        }

        return $result;
    }
}
