<?php
/**
 * Rating Plugin for Craft CMS
 *
 * @package   Rating
 * @author    Flipbox Factory
 * @copyright Copyright (c) 2015, Flipbox Digital
 * @link      https://flipboxfactory.com/craft/rating/
 * @license   https://flipboxfactory.com/craft/rating/license
 */

namespace craft\plugins\rating\helpers;

use Craft;
use craft\app\helpers\ArrayHelper;
use craft\app\helpers\StringHelper;

class Query
{

    /**
     * @var array
     */
    private static $_operators = ['not ', '!=', '<=', '>=', '<', '>', '='];


    /**
     * Standard param parsing.
     *
     * @param $value
     * @param $join
     * @return bool
     */
    public static function parseBaseParam(&$value, &$join)
    {

        // Force array
        $value = ArrayHelper::toArray($value);

        // Get join type ('and' , 'or')
        $join = static::_getJoinType($value);

        // Check for object array (via 'id' key)
        if($id = static::_findIdFromObjectArray($value)) {

            $value = [$id];

        }

        return false;

    }

    /**
     * Format the param value so that we return a string w/ a prepended operator.
     *
     * @param $value
     * @param $operator
     * @return string
     */
    public static function assembleParamValue($value, $operator)
    {

        // Handle arrays as values
        if (is_array($value)) {

            // Look for an 'id' key in an array
            if($id = static::_findIdFromObjectArray($value, $operator)) {

                // Prepend the operator
                return static::_prependOperator($id, $operator);

            }

            $value = static::_prependOperator($value, $operator);

            return StringHelper::toString($value);

        }

        return static::_prependOperator($value, $operator);

    }

    /**
     * Attempt to resolve a param value by the value.  Return false if a 'handle' or other string identifier is detected.
     *
     * @param $value
     * @param $operator
     * @return bool
     */
    public static function findParamValue(&$value, &$operator)
    {

        if (is_array($value)) {

            $value = static::assembleParamValue($value, $operator);

        } else {

            static::_normalizeEmptyValue($value);
            $operator = static::_parseParamOperator($value);

            if (is_numeric($value)) {

                $value = static::assembleParamValue($value, $operator);

            } elseif (StringHelper::toLowerCase($value) != ':empty:') {

                // Trim any whitespace from the value
                $value = trim($value);

                return false;

            }

        }

        return true;

    }

    /**
     * Get the join type
     *
     * @param $value
     * @return mixed|string
     */
    private static function _getJoinType(&$value)
    {

        // Get first value in array
        $joinType = ArrayHelper::getFirstValue($value);

        // Make sure first value is a string
        $firstVal = is_string($joinType) ? StringHelper::toLowerCase($joinType) : '';

        if ($firstVal == 'and' || $firstVal == 'or') {
            $join = array_shift($value);
        } else {
            $join = 'or';
        }

        return $join;

    }

    /**
     * Attempt to get a numeric value from an object array.
     * @param $value
     * @param null $operator
     * @return mixed|string
     */
    private static function _findIdFromObjectArray($value, $operator = null)
    {

        if($id = ArrayHelper::getValue($value, 'id', '')) {

            return static::_prependOperator($id, $operator);

        }

        return $id;

    }

    /**
     * Prepend the operator to a value
     *
     * @param $value
     * @param null $operator
     * @return string
     */
    private static function _prependOperator($value, $operator = null)
    {

        if($operator) {

            $operator = StringHelper::toLowerCase($operator);

            if(in_array($operator, static::$_operators)) {

                if(is_array($value)) {

                    $values = [];

                    foreach ($value as $v) {
                        $values[] = $operator . $v;
                    }

                    return $values;

                }

                return $operator . $value;
            }

        }

        return $value;

    }

    /**
     * Normalizes “empty” values.
     *
     * @param string &$value The param value.
     */
    private static function _normalizeEmptyValue(&$value)
    {
        if ($value === null) {
            $value = ':empty:';
        } else if (StringHelper::toLowerCase($value) == ':notempty:') {
            $value = 'not :empty:';
        }
    }

    /**
     * Extracts the operator from a DB param and returns it.
     *
     * @param string &$value Te param value.
     *
     * @return string The operator.
     */
    private static function _parseParamOperator(&$value)
    {
        foreach (static::$_operators as $testOperator) {
            // Does the value start with this operator?
            $operatorLength = strlen($testOperator);

            if (strncmp(StringHelper::toLowerCase($value), $testOperator,
                    $operatorLength) == 0
            ) {
                $value = mb_substr($value, $operatorLength);

                if ($testOperator == 'not ') {
                    return '!=';
                } else {
                    return $testOperator;
                }
            }
        }

        return '=';
    }
}
