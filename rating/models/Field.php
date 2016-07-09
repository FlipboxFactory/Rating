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

namespace craft\plugins\rating\models;

use Craft;
use craft\app\base\Model;
use craft\plugins\rating\elements\Rating as RatingElement;

class Field extends Model
{

    /**
     * @var string Value
     */
    public $value;

    /**
     * @var string Handle
     */
    public $id;

    /**
     * @var string Handle
     */
    public $handle;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var int Min rating value
     */
    public $min;

    /**
     * @var int Max rating value
     */
    public $max;

    /**
     * @var int Max rating value
     */
    public $increment;

    /**
     * @var int Max rating value
     */
    public $precision;

    /**
     * @var \DateTime Date updated
     */
    public $dateUpdated;

    /**
     * @var \DateTime Date created
     */
    public $dateCreated;

    /**
     * Set field value
     *
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get field value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @param RatingElement $element
     * @return mixed
     */
    public function prepareValue($value, RatingElement $element)
    {
        return $value;
    }

    /**
     * Get an array of options
     *
     * @return array
     */
    public function getOptions()
    {

        // Empty return option array
        $options = [];

        // Starting value
        $value = $this->min;

        // Iterate until we hit the max
        while ($value <= $this->max) {

            // Add to options
            $options[$value] = $value;

            // Increment
            $value = $value + $this->increment;

        }

        return $options;

    }

    /**
     * inherit
     */
    public function rules()
    {
        return [
            [
                ['value'],
                'number',
                'min' => $this->min,
                'max' => $this->max
            ]
        ];
    }

    /**
     * Direct access output value
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getValue();
    }

}
