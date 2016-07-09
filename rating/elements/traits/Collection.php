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

namespace craft\plugins\rating\elements\traits;

use Craft;
use craft\app\errors\Exception;
use craft\plugins\rating\models\Collection as CollectionModel;
use craft\plugins\rating\Plugin as RatingPlugin;

trait Collection
{
    /**
     * @var integer Collection ID
     */
    public $collectionId;

    /**
     * @var CollectionModel The cached collection model associated to this element. Set by [[getCollection()]].
     */
    private $collectionModel;

    /**
     * Identify whether element has an collection
     *
     * @return bool
     */
    public function hasCollection()
    {
        return $this->collectionModel instanceof CollectionModel;
    }

    /**
     * Get the collection of the element
     *
     * @param bool $strict
     * @return CollectionModel|null
     * @throws Exception
     */
    public function getCollection($strict = true)
    {

        // Check cache
        if (is_null($this->collectionModel)) {

            // Check property
            if (!empty($this->collectionId)) {

                // Find element
                if ($collectionElement = RatingPlugin::getInstance()->getCollection()->findById($this->collectionId)) {

                    // Set
                    $this->setCollection($collectionElement);

                } else {

                    // Clear property
                    $this->collectionId = null;

                    // Prevent subsequent look-ups
                    $this->collectionModel = false;

                    // Throw and exception?
                    if($strict) {

                        // Element not found
                        throw new Exception(Craft::t(
                            'app',
                            'Collection does not exist.'
                        ));

                    }

                }

            }

        } else {

            // Cache changed?
            if (($this->collectionId && $this->collectionModel === false) || ($this->collectionId !== $this->collectionModel->id)) {

                // Clear cache
                $this->collectionModel = null;

                // Again
                return $this->getCollection();

            }

        }

        return $this->hasCollection() ? $this->collectionModel : null;

    }

    /**
     * Associate an collection to the element
     *
     * @param $collection
     * @return $this
     */
    public function setCollection($collection)
    {

        // Clear cache
        $this->collectionModel = null;

        // Find collection
        if (!$collection = RatingPlugin::getInstance()->getCollection()->find($collection)) {

            // Clear property / cache
            $this->collectionId = $this->collectionModel = null;

        } else {

            // Set property
            $this->collectionId = $collection->id;

            // Set cache
            $this->collectionModel = $collection;

        }

        return $this;

    }

}
