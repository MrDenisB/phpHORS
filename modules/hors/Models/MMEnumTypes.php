<?php

/**
 * Description of BaseEnum
 *
 * @author seyfer
 */
abstract class BaseEnum
{

    /**
     * cache reflection request
     * @var array
     */
    protected static $constCache = [];
    public static $available     = [];

    /**
     * get class constant array
     * key = constant name, value = value
     * @return array
     */
    public static function getConstants()
    {
        if (empty(static::$constCache[get_called_class()])) {
            $reflect = new \ReflectionClass(get_called_class());
            static::$constCache[get_called_class()] = $reflect->getConstants();
        }

        return static::$constCache[get_called_class()];
    }

    /**
     * is there constant with name
     * @param string $name
     * @param boolean $strict
     * @return boolean
     */
    public static function isValidName($name, $strict = false)
    {
        $constants = static::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));

        return in_array(strtolower($name), $keys);
    }

    /**
     * is there cinstant with this value
     * @param string $value
     * @return boolean
     */
    public static function isValidValue($value)
    {
        $values = array_values(static::getConstants());

        return in_array($value, $values, $strict = true);
    }

    public static function isAvailable($name)
    {
        return in_array($name, static::$available);
    }

    public static function getAvailable()
    {
        return static::$available;
    }

    public static function setAvailable($available)
    {
        static::$available = $available;
    }

}
