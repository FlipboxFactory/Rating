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
use craft\app\elements\User as UserElement;
use craft\app\helpers\ArrayHelper;
use craft\plugins\rating\helpers\Query as QueryHelper;

trait Owner
{
    /**
     * @var integer Owner ID
     */
    public $ownerId = [];

    /**
     * Sets the [[ownerId]] property based on a given element owner.
     *
     * @param string|string[]|integer|integer[]|UserElement|UserElement[] $value The property value
     *
     * @return self The query object itself
     */
    public function setOwner($value)
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
                    if($element = Craft::$app->getUsers()->getUserByUsernameOrEmail($v)) {

                        $v = QueryHelper::assembleParamValue($element->id, $operator);

                    }

                }

            }

        }

        // parse param to allow for mixed variables
        $this->ownerId = array_merge([$join], ArrayHelper::filterEmptyStringsFromArray($value));

        return $this;

    }

    /**
     * Sets the [[ownerId]] property.
     *
     * @param string|string[]|integer|integer[]|UserElement|UserElement[] $owner The property value
     *
     * @return self The query object itself
     */
    public function owner($owner)
    {

        $this->setOwner($owner);

        return $this;

    }

    /**
     * Sets the [[ownerId]] property.
     *
     * @param integer|integer[] $owner The property value
     *
     * @return self The query object itself
     */
    public function ownerId($owner)
    {

        $this->ownerId = $owner;

        return $this;

    }

}
