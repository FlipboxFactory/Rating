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

namespace craft\plugins\rating\events\rating;

use craft\app\events\Event as BaseEvent;
use craft\plugins\rating\elements\Rating as RatingElement;

class Event extends BaseEvent
{
    /**
     * @var RatingElement The element associated with the event.
     */
    public $rating;
}
