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
use craft\plugins\rating\models\Field as FieldModel;

class Field extends Event
{
    /**
     * @var FieldModel The field associated with the event.
     */
    public $field;
}
