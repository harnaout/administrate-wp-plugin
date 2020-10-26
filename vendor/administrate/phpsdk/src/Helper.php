<?php

namespace Administrate\PhpSdk;

use Administrate\PhpSdk\GraphQl\QueryBuilder as QueryBuilder;

/**
 * Helper
 *
 * @package Administrate\PhpSdk
 * @author Jad Khater <jck@administrate.co>
 * @author Ali Habib <ahh@administrate.co>
 */
class Helper
{

    /**
    * Combine user args with default args.
    *
    * The defaults should be considered to be all of the args which are
    * supported by the caller and given as a list.
    * The returned attributes will only contain the attributes in the $defaults list.
    *
    * If the $args list has unsupported attributes, then they will be ignored and
    * removed from the final returned list.
    *
    * @since 2.5.0
    *
    * @param array  $defaults  Entire list of supported args and their defaults.
    * @param array  $args      User defined args.
    * @return array Combined and filtered args list.
    */
    public static function setArgs($defaults, $args)
    {
        $args = (array)$args;
        $out = array();
        foreach ($defaults as $name => $default) {
            if (array_key_exists($name, $args)) {
                $out[$name] = $args[$name];
            } else {
                $out[$name] = $default;
            }
        }
        return $out;
    }

    /**
    *Function to convert array into stdClass object
    * @param array
    * @return stdClass Object
    */
    public static function toObject($Array)
    {
        // Create new stdClass object
        $object = new class{
        };

        // Use loop to convert array into
        // stdClass object
        foreach ($Array as $key => $value) {
            if (is_array($value)) {
                $value = self::toObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }
}
