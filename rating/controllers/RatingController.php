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
use craft\app\helpers\ArrayHelper;
use craft\app\helpers\UrlHelper;
use craft\plugins\rating\elements\Rating as RatingElement;
use craft\plugins\rating\Plugin as RatingPlugin;

class RatingController extends Controller
{

    /**
     * Save a Rating
     *
     * @return \craft\app\web\Response
     * @throws \craft\app\errors\HttpException
     */
    public function actionSave()
    {

        // POST, PUT, PATCH
        $this->requirePostPutPatchRequest();

        // Optional attribute(s)
        $ratingId = Craft::$app->getRequest()->getBodyParam('id');

        // rating Id
        if ($ratingId) {

            $ratingElement = RatingPlugin::getInstance()->getRating()->getById($ratingId);

        } else {

            $ratingElement = new RatingElement();

            // Auto set owner if logged in
            if ($currentUser = Craft::$app->getUser()->getIdentity()) {
                $ratingElement->setOwner($currentUser);
            }

        }

        // Collection
        if (null !== ($collection = Craft::$app->getRequest()->getBodyParam('collection'))) {
            $ratingElement->setCollection($collection);
        }

        // Element
        if (null !== ($element = Craft::$app->getRequest()->getBodyParam('element'))) {
            if (is_array($element)) {
                $element = ArrayHelper::getFirstValue($element);
            }
            $ratingElement->setElement($element);
        }

        // Owner
        if (null !== ($owner = Craft::$app->getRequest()->getBodyParam('owner'))) {
            if (is_array($owner)) {
                $owner = ArrayHelper::getFirstValue($owner);
            }
            $ratingElement->setOwner($owner);
        }

        // Name
        $ratingElement->name = Craft::$app->getRequest()->getBodyParam('name',
            $ratingElement->name);

        // Email
        $ratingElement->email = Craft::$app->getRequest()->getBodyParam('email',
            $ratingElement->email);

        // Status
        $ratingElement->status = Craft::$app->getRequest()->getBodyParam('status',
            $ratingElement->getStatus());

        // Ratings
        $ratingElement->setRatingFieldValuesFromPost();

        // Content (if applicable)
        $ratingElement->setFieldValuesFromPost(Craft::$app->getRequest()->getBodyParam('namespace', 'fields'));

        // Save
        if (RatingPlugin::getInstance()->getRating()->save($ratingElement)) {

            // Success message
            $message = Craft::t('rating', 'Successfully saved rating.');

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
            return $this->redirectToPostedUrl($ratingElement);

        }

        // Fail message
        $message = Craft::t('rating', 'Failed to saved rating.');

        // Ajax request
        if (Craft::$app->getRequest()->isAjax) {

            return $this->asErrorJson(
                $ratingElement->getErrors()
            );

        }

        // Flash fail message
        Craft::$app->getSession()->setError($message);

        // Send the element back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'rating' => $ratingElement
        ]);

    }

    /**
     * Delete a Rating
     *
     * @return \craft\app\web\Response
     * @throws \craft\app\errors\HttpException
     */
    public function actionDelete()
    {

        // POST, DELETE
        $this->requirePostDeleteRequest();

        // Required attribute(s)
        $ratingId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        $ratingElement = RatingPlugin::getInstance()->getRating()->getById($ratingId);

        // Delete
        if (RatingPlugin::getInstance()->getRating()->delete($ratingElement)) {

            // Success message
            $message = Craft::t('rating', 'Successfully deleted rating.');

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
            return $this->redirectToPostedUrl($ratingElement);

        }

        // Fail message
        $message = Craft::t('rating', 'Failed to delete rating.');

        // Ajax request
        if (Craft::$app->getRequest()->isAjax) {

            return $this->asErrorJson(
                $ratingElement->getErrors()
            );

        }

        // Flash fail message
        Craft::$app->getSession()->setError($message);

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

        // Must have a collection configured
        if (empty($variables['collections'])) {

            // Redirect to collection settings
            $this->redirect($variables['baseCpPath'] . '/settings/collection');

        }

        return $this->renderTemplate('rating/_rating/index', $variables);

    }


    /**
     * Insert/Update
     *
     * @param null $ratingIdentifier
     * @param null $collectionIdentifier
     * @param null $rating
     * @return string
     */
    public function actionViewUpsert($collectionIdentifier, $ratingIdentifier = null, $rating = null)
    {

        // Empty variables for template
        $variables = [];

        // Apply base view variables
        $this->baseVariables($variables);

        // Get rating collection
        $collectionModel = RatingPlugin::getInstance()->getCollection()->get($collectionIdentifier);

        if (is_null($ratingIdentifier)) {

            // Append title
            $variables['title'] .= ': ' . Craft::t('rating', 'New');

            // Breadcrumbs
            $variables['crumbs'][] = [
                'label' => Craft::t('rating', 'New'),
                'url' => UrlHelper::getUrl($variables['baseCpPath'])
            ];

            // Element
            $variables['rating'] = is_null($rating) ? new RatingElement() : $rating;

        } else {

            // Append title
            $variables['title'] .= ': ' . Craft::t('rating', 'Edit');

            // Breadcrumbs
            $variables['crumbs'][] = [
                'label' => Craft::t('rating', 'Edit'),
                'url' => UrlHelper::getUrl($variables['baseCpPath'])
            ];

            // Element
            $variables['rating'] = is_null($rating) ? RatingPlugin::getInstance()->getRating()->get($ratingIdentifier) : $rating;

        }

        // Set property
        $variables['rating']->setCollection($collectionModel);


        // Set the "Continue Editing" URL
        $variables['continueEditingUrl'] = $variables['baseCpPath'] . '/' . $collectionIdentifier . '/{id}';

        // Set the "Save and add another" URL
        $variables['newRecordUrl'] = $variables['baseCpPath'] . '/' . $collectionIdentifier . '/new';

        return $this->renderTemplate('rating/_rating/_upsert', $variables);

    }

    /**
     * Set base variables used to generate template views
     *
     * @param array $variables
     */
    protected function baseVariables(array &$variables = [])
    {

        // Get base variables
        parent::baseVariables($variables);

        // Selected tab
        $variables['selectedTab'] = 'rating';

        // Plugin settings
        $settings = RatingPlugin::getInstance()->getSettings();

        // Find all comment statuses
        $variables['statuses'] = $settings->getStatuses();

        // Create comment status option array
        $variables['statusOptions'] = [];
        foreach ($variables['statuses'] as $statusKey => $statusLabel) {
            $variables['statusOptions'][] = [
                'label' => Craft::t('site', $statusLabel),
                'value' => $statusKey
            ];
        }

        // Find all rating collections
        $variables['collections'] = RatingPlugin::getInstance()->getCollection()->findAll();

        // Create rating collections option array
        $variables['collectionOptions'] = [];
        foreach ($variables['collections'] as $collection) {
            $variables['collectionOptions'][] = [
                'label' => Craft::t('site', $collection->name),
                'value' => $collection->id
            ];
        }

    }

}
