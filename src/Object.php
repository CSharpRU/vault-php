<?php

namespace Vault;

use Vault\Helpers\ArrayHelper;

/**
 * Class Object
 *
 * @package Vault
 */
class Object
{
    /**
     * Constructor.
     * The default implementation does one things:
     *
     * - Initializes the object with the given configuration `$config`.
     *
     * If this method is overridden in a child class, it is recommended that
     *
     * - the last parameter of the constructor is a configuration array, like `$config` here.
     * - call the parent implementation at the end of the constructor.
     *
     * @param array $config name-value pairs that will be used to initialize the object properties
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
     * Returns a value indicating whether a property can be set.
     * A property is writable if:
     *
     * - the class has a setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string  $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     *
     * @return boolean whether the property can be written
     * @see canGetProperty
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * Returns the value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $object->property;`.
     *
     * @param string $name the property name
     *
     * @return mixed the property value
     * @throws \RuntimeException if the property is not defined
     * @see __set
     */
    public function __get($name)
    {
        $getter = 'get' . $name;

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        if (method_exists($this, 'set' . $name)) {
            throw new \RuntimeException('Getting write-only property: ' . get_class($this) . '::' . $name);
        }

        throw new \RuntimeException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Sets value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     *
     * @param string $name the property name or the event name
     * @param mixed  $value the property value
     *
     * @throws \RuntimeException if the property is not defined
     * @throws \RuntimeException if the property is read-only.
     * @see __get
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;

        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new \RuntimeException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new \RuntimeException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Checks if the named property is set (not null).
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `isset($object->property)`.
     *
     * Note that if the property is not defined, false will be returned.
     *
     * @param string $name the property name or the event name
     *
     * @return boolean whether the named property is set (not null).
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
     * Sets an object property to null.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `unset($object->property)`.
     *
     * Note that if the property is not defined, this method will do nothing.
     * If the property is read-only, it will throw an exception.
     *
     * @param string $name the property name
     *
     * @throws \RuntimeException if the property is read only.
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;

        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new \RuntimeException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Calls the named method which is not a class method.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     *
     * @param string $name the method name
     * @param array  $params method parameters
     *
     * @throws \RuntimeException when calling unknown method
     * @return mixed the method return value
     */
    public function __call($name, $params)
    {
        throw new \RuntimeException('Unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * Returns a value indicating whether a property is defined.
     * A property is defined if:
     *
     * - the class has a getter or setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string  $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     *
     * @return boolean whether the property is defined
     * @see canGetProperty
     * @see canSetProperty
     */
    public function hasProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    /**
     * Returns a value indicating whether a property can be read.
     * A property is readable if:
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string  $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     *
     * @return boolean whether the property can be read
     * @see canSetProperty
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * Returns a value indicating whether a method is defined.
     *
     * The default implementation is a call to php function `method_exists()`.
     * You may override this method when you implemented the php magic method `__call()`.
     *
     * @param string $name the property name
     *
     * @return boolean whether the property is defined
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }

    /**
     * Converts the object into an array.
     * The default implementation will return all public property values as an array.
     *
     * @param array $fields
     * @param array $expand
     * @param bool  $recursive
     *
     * @return array the array representation of the object
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = [];

        foreach ($this->resolveFields($fields, $expand) as $field => $definition) {
            $data[$field] = is_string($definition) ? $this->$definition : call_user_func($definition, $this, $field);
        }

        return $recursive ? ArrayHelper::toArray($data) : $data;
    }

    /**
     * @param array $fields the fields being requested for exporting
     * @param array $expand the additional fields being requested for exporting
     *
     * @return array
     */
    protected function resolveFields(array $fields, array $expand)
    {
        $result = [];

        foreach ($this->fields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (empty($fields) || in_array($field, $fields, true)) {
                $result[$field] = $definition;
            }
        }

        if (empty($expand)) {
            return $result;
        }

        foreach ($this->extraFields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (in_array($field, $expand, true)) {
                $result[$field] = $definition;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = array_keys(get_object_vars($this));

        return array_combine($fields, $fields);
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [];
    }
}