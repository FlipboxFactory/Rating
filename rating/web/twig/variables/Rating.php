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

namespace craft\plugins\rating\web\twig\variables;

use craft\plugins\rating\elements\db\Rating as RatingQuery;
use craft\plugins\rating\elements\Rating as RatingElement;
use craft\plugins\rating\models\Stats as StatsModel;

class Rating
{

    /**
     * Configure a rating query.
     *
     * @param mixed $criteria
     * @return RatingQuery
     */
    public function query($criteria = null)
    {
        return RatingElement::find()->configure($criteria);
    }

    /**
     * Alias to rating query
     *
     * @param mixed $criteria
     * @return RatingQuery
     */
    public function find($criteria = null)
    {
        return $this->query($criteria);
    }

    /**
     * Get a new rating element
     *
     * @param null $criteria
     * @return RatingElement
     */
    public function create($criteria = null)
    {
        return new RatingElement($criteria);
    }

    /**
     * Returns a new RatingQuery instance.
     *
     * @param mixed $criteria
     *
     * @return RatingQuery
     */
    public function stats($criteria = null)
    {

        $statsModel = new StatsModel();

        $ratingQuery = RatingElement::find()->configure($criteria);

        $statsModel->setQuery($ratingQuery);

        return $statsModel;

    }

    /**
     * Sub-Variables that are accessed 'craft.rating.settings'
     *
     * @return Settings
     */
    public function settings()
    {
        return new Settings();
    }

    /**
     * Sub-Variables that are accessed 'craft.rating.collection'
     *
     * @return Collection
     */
    public function collection()
    {
        return new Collection();
    }

}