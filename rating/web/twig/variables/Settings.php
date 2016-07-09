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

class Settings
{

    /**
     * Get status options as ['value' => '', 'label' => '']
     *
     * @return array
     */
    public function getStatusOptions()
    {

        $statusArray = $this->getStatuses();

        $returnArray = [];

        foreach ($statusArray as $value => $label) {
            $returnArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $returnArray;

    }

    /**
     * Get status settings as $key => $value
     *
     * @return array
     */
    public function getStatuses()
    {
        return RatingPlugin::getInstance()->getSettings()->getStatuses();
    }

    /**
     * @return mixed
     */
    public function getDefaultStatus()
    {
        return RatingPlugin::getINstance()->getSettings()->defaultStatus();
    }

}