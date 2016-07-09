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

namespace craft\plugins\rating\controllers\settings;

use Craft;
use craft\app\helpers\UrlHelper;
use craft\plugins\rating\controllers\Controller as BaseController;

abstract class Controller extends BaseController
{

    /**
     * Set base variables used to generate template views
     *
     * @param array $variables
     */
    protected function baseVariables(array &$variables = [])
    {

        // Get base variables
        parent::baseVariables($variables);

        // Page title
        $variables['title'] .= ' ' . Craft::t('rating', 'Settings');

        // Selected tab
        $variables['selectedTab'] = 'settings';

        // Path to controller actions
        $variables['baseActionPath'] .= '/settings';

        // Path to CP
        $variables['baseCpPath'] .= '/settings';

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $variables['baseCpPath'];

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('rating', 'Settings'),
            'url' => UrlHelper::getUrl($variables['baseCpPath'])
        ];

    }

}