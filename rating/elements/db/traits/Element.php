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

namespace craft\plugins\rating\elements\db\traits;

use Craft;
use craft\app\base\ElementInterface;
use craft\app\helpers\ArrayHelper;
use craft\plugins\rating\helpers\Query as QueryHelper;

trait Element
{
    /**
     * @var integer Element Id
     */
    public $elementId;

    /**
     * Sets the [[elementId]] property based on a given element.
     *
     * @param string|string[]|integer|integer[]|ElementInterface|ElementInterface[] $value The property value
     *
     * @return self The query object itself
     */
    public function setElement($value)
    {

        // Default join type
        $join = 'or';

        // Parse as single param?
        if(false === QueryHelper::parseBaseParam($value, $join)) {

            // Add one by one
            foreach($value as $operator => &$v) {

                // attempt to assemble value (return false if it's a handle)
                if(false === QueryHelper::findParamValue($v, $operator)) {

                    // create new query
                    if($element = Craft::$app->getElements()->getElementByUri($v)) {

                        $v = QueryHelper::assembleParamValue($element->id, $operator);

                    }

                }

            }

        }

        // parse param to allow for mixed variables
        $this->elementId = array_merge([$join], ArrayHelper::filterEmptyStringsFromArray($value));

        return $this;

    }

    /**
     * Sets the [[elementId]] property.
     *
     * @param string|string[]|integer|integer[]|ElementInterface|ElementInterface[] $element The property value
     *
     * @return self The query object itself
     */
    public function element($element)
    {

        $this->setElement($element);

        return $this;

    }

    /**
     * Sets the [[elementId]] property.
     *
     * @param integer|integer[] $element The property value
     *
     * @return self The query object itself
     */
    public function elementId($element)
    {

        $this->elementId = $element;

        return $this;

    }

}
