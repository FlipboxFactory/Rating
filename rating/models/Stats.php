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
use craft\plugins\rating\elements\db\Rating as RatingQuery;
use craft\plugins\rating\services\Field as FieldService;

class Stats extends Model
{

    /**
     * @var RatingQuery
     */
    private $query;

    public function setQuery(RatingQuery $ratingQuery)
    {
        $this->query = $ratingQuery;
    }

    /**
     * Get query count
     *
     * @param null $identifier
     * @return int|string
     */
    public function getCount($identifier = null)
    {
        if (!is_null($identifier)) {
            return $this->query->count(FieldService::FIELD_PREFIX . $identifier);
        }

        return $this->query->count();
    }

    /**
     * Get query average
     *
     * @param null $identifier
     * @return mixed
     */
    public function getAverage($identifier = null)
    {
        if (!is_null($identifier)) {
            return $this->query->average(FieldService::FIELD_PREFIX . $identifier);
        }

        return $this->query->average($identifier);
    }

    /**
     * Get query sum
     *
     * @param null $identifier
     * @return mixed
     */
    public function getSum($identifier = null)
    {
        if (!is_null($identifier)) {
            return $this->query->sum(FieldService::FIELD_PREFIX . $identifier);
        }

        return $this->query->sum($identifier);
    }

    /**
     * Get query min
     *
     * @param null $identifier
     * @return mixed
     */
    public function getMin($identifier = null)
    {
        if (!is_null($identifier)) {
            return $this->query->min(FieldService::FIELD_PREFIX . $identifier);
        }

        return $this->query->min($identifier);
    }

    /**
     * Get query max
     *
     * @param null $identifier
     * @return mixed
     */
    public function getMax($identifier = null)
    {
        if (!is_null($identifier)) {
            return $this->query->max(FieldService::FIELD_PREFIX . $identifier);
        }

        return $this->query->max($identifier);
    }

}
