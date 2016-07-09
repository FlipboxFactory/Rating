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

use craft\plugins\rating\elements\Rating as RatingElement;

class Delete extends Event
{

    /**
     * @var RatingElement The element that the deleted element's content is getting transferred to.
     */
    public $transferTo;

}
