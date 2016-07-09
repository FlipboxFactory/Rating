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

namespace craft\plugins\rating\controllers;

use Craft;
use craft\app\errors\HttpException;
use craft\app\helpers\UrlHelper;
use craft\app\web\Controller as AppController;
use craft\plugins\rating\Plugin as RatingPlugin;

abstract class Controller extends AppController
{

    /**
     * Throws a 400 error if this isn’t a POST, PUT, or PATCH request
     *
     * @throws HttpException
     * @return void
     */
    public function requirePostPutPatchRequest()
    {
        if (!(Craft::$app->getRequest()->getIsPost() || !Craft::$app->getRequest()->getIsPatch() || !Craft::$app->getRequest()->getIsPut())) {
            throw new HttpException(400);
        }
    }

    /**
     * Throws a 400 error if this isn’t a POST or DELETE request
     *
     * @throws HttpException
     * @return void
     */
    public function requirePostDeleteRequest()
    {
        if (!(Craft::$app->getRequest()->getIsPost() || !Craft::$app->getRequest()->getIsDelete())) {
            throw new HttpException(400);
        }
    }

    /**
     * Set base variables used to generate template views
     *
     * @param array $variables
     */
    protected function baseVariables(array &$variables = [])
    {

        // Page title
        $variables['title'] = Craft::t('rating', 'Rating');

        // Path to controller actions
        $variables['baseActionPath'] = 'rating';

        // Path to CP
        $variables['baseCpPath'] = 'rating';

        // Plugin settings
        $variables['settings'] = RatingPlugin::getInstance()->getSettings();

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => $variables['title'],
            'url' => UrlHelper::getUrl($variables['baseCpPath'])
        ];

    }

}
