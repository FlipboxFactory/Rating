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

use craft\plugins\rating\Plugin as RatingPlugin;

class Collection
{

    public function find($identifier)
    {
        return RatingPlugin::getInstance()->getCollection()->find($identifier);
    }

}