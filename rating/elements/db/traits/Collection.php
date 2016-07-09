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
use craft\app\helpers\ArrayHelper;
use craft\app\helpers\DbHelper;
use craft\app\db\Query as DbQuery;
use craft\plugins\rating\helpers\Query as QueryHelper;
use craft\plugins\rating\models\Collection as CollectionModel;
use craft\plugins\rating\records\Collection as CollectionRecord;

trait Collection
{
    /**
     * @var integer Collection ID
     */
    public $collectionId;

    /**
     * Sets the [[collectionId]] property based on a given element collection(s)’s.
     *
     * @param string|string[]|integer|integer[]|CollectionModel|CollectionModel[] $value The property value
     *
     * @return self The query object itself
     */
    public function setCollection($value)
    {

        // Default join type
        $join = 'or';

        // Parse as single param?
        if(false === QueryHelper::parseBaseParam($value, $join)) {

            // Add one by one
            foreach($value as $operator => &$v) {

                // attempt to assemble value (return false if it's a handle)
                if(false === QueryHelper::findParamValue($v, $operator)) {

                    /// create new query
                    $query = new DbQuery();
                    $ids = $query->select('id')
                        ->from(CollectionRecord::tableName())
                        ->where(DbHelper::parseParam('handle', $v, $query->params))
                        ->column();

                    $v = QueryHelper::assembleParamValue($ids, $operator);

                }

            }

        }

        // parse param to allow for mixed variables
        $this->collectionId = array_merge([$join], ArrayHelper::filterEmptyStringsFromArray($value));

        return $this;

    }

    /**
     * Alias set property for chain setting
     *
     * @param string|string[]|integer|integer[]|CollectionModel|CollectionModel[] $value The property value
     *
     * @return self The query object itself
     */
    public function collection($value)
    {

        $this->setCollection($value);

        return $this;

    }

    /**
     * Alias set property for chain setting
     *
     * @param integer|integer[] $value The property value
     *
     * @return self The query object itself
     */
    public function collectionId($value)
    {

        $this->collectionId = $value;

        return $this;

    }

}
