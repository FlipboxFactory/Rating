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
use craft\app\helpers\ArrayHelper;
use craft\plugins\rating\Plugin as RatingPlugin;

class GeneralController extends Controller
{

    /**
     * Save general plugin settings
     *
     * @return \craft\app\web\Response
     * @throws \craft\app\errors\HttpException
     */
    public function actionSave()
    {

        // Admins
        $this->requireAdmin();

        // POST, PUT, PATCH
        $this->requirePostPutPatchRequest();

        // Settings model
        $settingsModel = RatingPlugin::getInstance()->getSettings();

        $statusArray = [];

        // Statuses from post
        if ($rawStatuses = Craft::$app->getRequest()->getBodyParam('statuses', [])) {

            foreach (ArrayHelper::toArray($rawStatuses) as $rawStatus) {

                // Make sure we have a label and value
                if (empty($rawStatus['label']) || empty($rawStatus['value'])) {

                    // Error already exists?
                    if (!$settingsModel->hasErrors('statuses')) {

                        $settingsModel->addError('statuses',
                            Craft::t('rating', 'Each status must have a valid label and value.'));

                    }

                }

                // Formatted status
                $statusArray[$rawStatus['value']] = $rawStatus['label'];

            }

        }

        // Set settings array
        $settingsModel->statuses = !empty($statusArray) ? $statusArray : null;

        // Save settings
        if (!$settingsModel->hasErrors() && RatingPlugin::getInstance()->getSetting()->save($settingsModel)) {

            // Success message
            $message = Craft::t('rating', 'Settings saved successfully.');

            // Ajax request
            if (Craft::$app->getRequest()->isAjax) {

                return $this->asJson([
                    'success' => true,
                    'message' => $message
                ]);

            }

            // Flash success message
            Craft::$app->getSession()->setNotice($message);

            // Redirect
            return $this->redirectToPostedUrl($settingsModel);

        }

        // Fail message
        $message = Craft::t('rating', 'Settings NOT saved successfully.');

        // Ajax request
        if (Craft::$app->getRequest()->isAjax) {

            return $this->asErrorJson(
                $settingsModel->getErrors()
            );

        }

        // Flash fail message
        Craft::$app->getSession()->setError($message);

        // Send settings back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'settings' => $settingsModel
        ]);

    }


    /*******************************************
     * VIEWS
     *******************************************/

    /**
     * Index
     *
     * @return string
     */
    public function actionViewIndex()
    {

        // Empty variables for template
        $variables = [];

        // apply base view variables
        $this->baseVariables($variables);

        return $this->renderTemplate('rating/_settings', $variables);

    }


    /*******************************************
     * VARIABLES
     *******************************************/

    /**
     * @inheritdoc
     */
    protected function baseVariables(array &$variables = [])
    {

        // Get base variables
        parent::baseVariables($variables);

        // Path to controller actions
        $variables['baseActionPath'] .= '/general';

    }

}
