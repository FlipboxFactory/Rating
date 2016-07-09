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
use craft\app\helpers\UrlHelper;
use craft\plugins\comment\elements\Comment;
use craft\plugins\organization\elements\Organization;
use craft\plugins\rating\models\Collection as CollectionModel;
use craft\plugins\rating\Plugin as RatingPlugin;

class CollectionController extends Controller
{

    /**
     * Save rating collection
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

        // Optional attributes
        $id = Craft::$app->getRequest()->getBodyParam('id');

        // Required attributes
        $name = Craft::$app->getRequest()->getRequiredBodyParam('name');
        $handle = Craft::$app->getRequest()->getRequiredBodyParam('handle');
        $elementType = Craft::$app->getRequest()->getRequiredBodyParam('elementType');
        $ratingFields = Craft::$app->getRequest()->getRequiredBodyParam('ratingFields', []);

        if (!is_null($id)) {

            $collectionModel = RatingPlugin::getInstance()->getCollection()->get($id);

        } else {

            $collectionModel = new CollectionModel();

        }

        // Set properties
        $collectionModel->name = $name;
        $collectionModel->handle = $handle;
        $collectionModel->elementType = $elementType;

        // Get rating fields from post
        $ratingFieldModels = [];
        foreach (ArrayHelper::toArray($ratingFields) as $ratingFieldId) {
            $ratingFieldModels[] = RatingPlugin::getInstance()->getField()->getById($ratingFieldId);
        }

        // Set rating fields
        $collectionModel->setRatingFields($ratingFieldModels);

        // Set field layout
        $collectionModel->setFieldLayout(Craft::$app->getFields()->assembleLayoutFromPost());

        // Save
        if (RatingPlugin::getInstance()->getCollection()->save($collectionModel)) {

            // Success message
            $message = Craft::t('rating', 'Collection saved successfully.');

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
            return $this->redirectToPostedUrl($collectionModel);

        }

        // Fail message
        $message = Craft::t('rating', 'Collection NOT saved successfully.');

        // Ajax request
        if (Craft::$app->getRequest()->isAjax) {

            return $this->asErrorJson(
                $collectionModel->getErrors()
            );

        }

        // Flash fail message
        Craft::$app->getSession()->setError($message);

        // Send the model back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'collection' => $collectionModel
        ]);

    }


    /**
     * Delete rating collection
     *
     * @return \craft\app\web\Response
     * @throws \craft\app\errors\HttpException
     */
    public function actionDelete()
    {

        // Admins
        $this->requireAdmin();

        // POST, PUT, PATCH
        $this->requirePostDeleteRequest();

        // Required attribute(s)
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');

        // Get model
        $collectionModel = RatingPlugin::getInstance()->getCollection()->get($id);

        // Delete
        if (RatingPlugin::getInstance()->getCollection()->delete($collectionModel)) {

            // Success message
            $message = Craft::t('rating', 'Collection deleted successfully.');

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
            return $this->redirectToPostedUrl($collectionModel);

        }

        // Fail message
        $message = Craft::t('rating', 'Collection NOT deleted successfully.');

        // Ajax request
        if (Craft::$app->getRequest()->isAjax) {

            return $this->asErrorJson(
                $collectionModel->getErrors()
            );

        }

        // Flash fail message
        Craft::$app->getSession()->setError($message);

        // Send the model back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'collection' => $collectionModel
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

        // Find all rating collections
        $variables['collections'] = RatingPlugin::getInstance()->getCollection()->findAll();

        return $this->renderTemplate('rating/_settings/collection', $variables);

    }

    /**
     * Insert/Update
     *
     * @param null $collectionIdentifier
     * @param CollectionModel $collection
     * @return string
     */
    public function actionViewUpsert($collectionIdentifier = null, CollectionModel $collection = null)
    {

        // Empty variables for template
        $variables = [];

        // Apply base view variables
        $this->baseVariables($variables);

        // Check if collection is already set (failures, etc).
        if (is_null($collection)) {

            // Look for collection id
            if (!empty($collectionIdentifier)) {

                $collection = RatingPlugin::getInstance()->getCollection()->get($collectionIdentifier);

            } else {

                $collection = new CollectionModel();

            }

        }

        // Get Elements
        $variables['elementTypes'] = Craft::$app->getElements()->getAllElementTypes();
        // todo - remove this once the method above supports plugins
        $variables['elementTypes'][] = Organization::className();
        $variables['elementTypes'][] = Comment::className();

        $variables['elementTypeOptions'] = [];
        foreach ($variables['elementTypes'] as $elementType) {
            $variables['elementTypeOptions'][] = [
                'label' => $elementType::displayName(),
                'value' => $elementType,
            ];
        }

        // Get rating fields
        $variables['ratingFields'] = RatingPlugin::getInstance()->getField()->findAll();

        $variables['ratingFieldOptions'] = [];
        foreach ($variables['ratingFields'] as $ratingField) {
            $variables['ratingFieldOptions'][] = [
                'label' => $ratingField->name,
                'value' => $ratingField->id,
            ];
        }

        // Set collection model
        $variables['collection'] = $collection;

        // If new model
        if (!$collection->id) {

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
            $variables['title'] .= " - " . $variables['collection']->name;

            // Append breadcrumb
            $variables['crumbs'][] = [
                'label' => $variables['collection']->name,
                'url' => UrlHelper::getUrl($variables['baseCpPath'] . '/' . $variables['collection']->id)
            ];

            // Set the "Continue Editing" URL
            $variables['continueEditingUrl'] .= '/' . $variables['collection']->id;

        }

        return $this->renderTemplate('rating/_settings/collection/_upsert', $variables);

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
        $variables['title'] .= ': Collections';

        // Path to controller actions
        $variables['baseActionPath'] .= '/collection';

        // Path to CP
        $variables['baseCpPath'] .= '/collection';

        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $variables['baseCpPath'];

        // Breadcrumbs
        $variables['crumbs'][] = [
            'label' => Craft::t('rating', 'Collections'),
            'url' => UrlHelper::getUrl($variables['baseCpPath'])
        ];

    }

}
