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

namespace craft\plugins\rating\events;

use craft\app\events\Event;
use craft\plugins\rating\models\Collection as CollectionModel;

class Collection extends Event
{
    /**
     * @var CollectionModel The collection associated with the event.
     */
    public $collection;

    /**
     * @var CollectionModel The existing collection that is associated with the event.
     */
    public $existingCollection;
}
