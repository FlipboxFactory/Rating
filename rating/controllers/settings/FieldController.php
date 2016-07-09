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
use craft\plugins\rating\models\Field as FieldModel;
use craft\plugins\rating\Plugin as RatingPlugin;

class FieldController extends Controller
{

    /**
     * Save rating field
     *
     * @return \craft\app\web\Response
     * @throws \craft\app\errors\HttpException
     */
    public function actionSave()
    {

        // Admins and Post requests only
        $this->requireAdmin();
        $this->requirePostRequest();

        // Optional attributes
        $id = Craft::$app->getRequest()->getBodyParam('id');

        // Required attributes
        $name = Craft::$app->getRequest()->getRequiredBodyParam('name');
        $handle = Craft::$app->getRequest()->getRequiredBodyParam('handle');
        $min = Craft::$app->getRequest()->getRequiredBodyParam('min');
        $max = Craft::$app->getRequest()->getRequiredBodyParam('max');
        $increment = Craft::$app->getRequest()->getRequiredBodyParam('increment');
        $precision = Craft::$app->getRequest()->getRequiredBodyParam('precision');

        if (!is_null($id)) {

            $fieldModel = RatingPlugin::getInstance()->getField()->get($id);

        } else {

            $fieldModel = new FieldModel();

        }

        // Set properties
        $fieldModel->name = $name;
        $fieldModel->handle = $handle;
        $fieldModel->min = $min;
        $fieldModel->max = $max;
        $fieldModel->increment = $increment;
        $fieldModel->precision = $precision;

        // Save
        if (RatingPlugin::getInstance()->getField()->save($fieldModel)) {

            // Success message
            $message = Craft::t('rating', 'Rating field saved successfully.');

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
            return $this->redirectToPostedUrl($fieldModel);

        }

        // Fail message
        $message = Craft::t('rating', 'Rating field NOT saved successfully.');

        // Ajax request
        if (Craft::$app->getRequest()->isAjax) {

            return $this->asErrorJson(
                $fieldModel->getErrors()
            );

        }

        // Flash fail message
        Craft::$app->getSession()->setError($message);

        // Send the model back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'field' => $fieldModel
        ]);

    }


    /**
     * Delete rating field
     *
     * @return \craft\app\web\Response
     * @throws \craft\app\errors\HttpException
     */
    public function actionDelete()
    {

        // Admins and Post requests only
        $this->requireAdmin();
        $this->requirePostRequest();

        // Required attribute(s)
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        // Get model
        $fieldModel = RatingPlugin::getInstance()->getField()->get($id);

        // Delete
        if (RatingPlugin::getInstance()->getField()->delete($fieldModel)) {

            // Success message
            $message = Craft::t('rating', 'Rating field deleted successfully.');

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
            return $this->redirectToPostedUrl($fieldModel);

        }

        // Fail message
        $message = Craft::t('rating', 'Rating field NOT deleted successfully.');

        // Ajax request
        if (Craft::$app->getRequest()->isAjax) {

            return $this->asErrorJson(
                $fieldModel->getErrors()
            );

        }

        // Flash fail message
        Craft::$app->getSession()->setError($message);

        // Send the model back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'field' => $fieldModel
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

        // Find all rating fields
        $variables['fields'] = RatingPlugin::getInstance()->getField()->findAll();

        return $this->renderTemplate('rating/_settings/field', $variables);

    }

    /**
     * Insert/Update
     *
     * @param null $fieldIdentifier
     * @param FieldModel $fieldModel
     * @return string
     */
    public function actionViewUpsert($fieldIdentifier = null, FieldModel $fieldModel = null)
    {

        // Empty variables for template
        $variables = [];

        // Apply base view variables
        $this->baseVariables($variables);

        // Flag new
        $variables['brandNew'] = false;

        // Check if field is already set (failures, etc).
        if (is_null($fieldModel)) {

            // Look for field id
            if (!empty($fieldIdentifier)) {

                $fieldModel = RatingPlugin::getInstance()->getField()->get($fieldIdentifier);

            } else {

                $fieldModel = new FieldModel();

                $variables['brandNew'] = true;

            }

        }

        // Set field model
        $variables['field'] = $fieldModel;

        // If new model
        if ($variables['brandNew']) {

            // Append title
            $variables['title'] .= " - " . Craft::t('rating', 'New');

            // Append breadcrumb
            $variables['crumbs'][] = [
                'label' => Craft::t('rating', 'New'),
                'url' => UrlHelper::getUrl($variables['baseCpPath'] . '/new')
            ];

            // Set the "Continue Editing" URL
            $variables['continueEditingUrl'] .= '/{id}';

        } else {

            // Append title
            $variables['title'] .= " - " . $variables['field']->name;

            // Append breadcrumb
            $variables['crumbs'][] = [
                'label' => $variables['field']->name,
                'url' => UrlHelper::getUrl($variables['baseCpPath'] . '/' . $variables['field']->id)
            ];

            // Set the "Continue Editing" URL
            $variables['continueEditingUrl'] .= '/' . $variables['field']->id;

        }

        return $this->renderTemplate('rating/_settings/field/_upsert', $variables);

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

        // Page title
        $variables['title'] .= ': Fields';

        // Path to controller actions
        $variables['baseActionPath'] .= '/field';

        // Path to CP
        $variables['baseCpPath'] .= '/field';

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $variables['baseCpPath'];

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('rating', 'Fields'),
            'url' => UrlHelper::getUrl($variables['baseCpPath'])
        ];

    }

}
