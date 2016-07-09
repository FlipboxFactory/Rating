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

namespace craft\plugins\rating\services;

use Craft;
use craft\app\errors\Exception;
use craft\plugins\rating\events\Collection as CollectionEvent;
use craft\plugins\rating\models\Collection as CollectionModel;
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\records\Collection as CollectionRecord;
use yii\base\Component;

class Collection extends Component
{

    /** Common model interactions */
    use traits\Model;

    /**
     * @event CollectionEvent The event that is triggered before an rating collection is created.
     *
     * You may set [[CollectionEvent::isValid]] to `false` to prevent the rating collection from getting created.
     */
    const EVENT_BEFORE_CREATE = 'beforeCreate';

    /**
     * @event CollectionEvent The event that is triggered after a rating collection is created.
     *
     * * You may set [[CollectionEvent::isValid]] to `false` to prevent the rating collection from getting created.
     */
    const EVENT_AFTER_CREATE = 'afterCreate';

    /**
     * @event CollectionEvent The event that is triggered before an rating collection is updated.
     *
     * You may set [[CollectionEvent::isValid]] to `false` to prevent the rating collection from getting updated.
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';

    /**
     * @event CollectionEvent The event that is triggered after a rating collection is updated.
     *
     * * You may set [[CollectionEvent::isValid]] to `false` to prevent the rating collection from getting updated.
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';

    /**
     * @event CollectionEvent The event that is triggered before an rating collection is deleted.
     *
     * You may set [[CollectionEvent::isValid]] to `false` to prevent the rating collection from getting deleted.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @event CollectionEvent The event that is triggered after a rating collection is deleted.
     *
     * * You may set [[CollectionEvent::isValid]] to `false` to prevent the rating collection from getting deleted.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';


    /*******************************************
     * RECORDS
     *******************************************/

    /**
     * Get a record (when you explicitly require one)
     *
     * @param $condition
     * @return CollectionRecord
     * @throws Exception
     */
    protected function getRecord($condition)
    {

        if (!$record = $this->findRecord($condition)) {
            $this->notFoundException();
        }

        return $record;

    }

    /**
     * Find an array of records
     *
     * @param null $condition
     * @return array|CollectionRecord[]
     */
    protected function findAllRecords($condition = null)
    {
        if (is_null($condition)) {
            return CollectionRecord::find()->all();
        }

        return CollectionRecord::findAll($condition);
    }

    /**
     * Find a record
     *
     * @param $condition
     * @return null|CollectionRecord
     */
    protected function findRecord($condition)
    {
        return CollectionRecord::findOne($condition);
    }

    /*******************************************
     * MODELS
     *******************************************/

    /**
     * @param $config
     * @return CollectionModel
     */
    protected function newModel($config)
    {
        return CollectionModel::create($config);
    }

    /*******************************************
     * FIELDS
     *******************************************/

    /**
     * @param CollectionModel $collectionModel
     * @return array
     */
    public function getRatingFields(CollectionModel $collectionModel)
    {
        return RatingPlugin::getInstance()->getCollectionField()->getAllByCollection($collectionModel);
    }

    /*******************************************
     * SAVE
     *******************************************/

    /**
     * Save a new or existing rating collection.
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     */
    public function save(CollectionModel $collectionModel)
    {

        if (!$collectionModel->id) {

            return $this->create($collectionModel);

        }

        return $this->update($collectionModel);

    }

    /*******************************************
     * CREATE
     *******************************************/

    /**
     * Create new rating collection.
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function create(CollectionModel $collectionModel)
    {

        // Ensure we're creating a record
        if ($collectionModel->id) {

            return $this->update($collectionModel);

        }

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new CollectionEvent([
                'collection' => $collectionModel
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_CREATE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // New record
                $collectionRecord = new CollectionRecord();

                // Transfer attributes to record
                $this->transferFromModelToRecord($collectionModel, $collectionRecord);

                // Get layout
                $fieldLayout = $collectionModel->getFieldLayout();

                // Save layout
                if (Craft::$app->getFields()->saveLayout($fieldLayout)) {

                    // Transfer layout id
                    $collectionModel->fieldLayoutId = $collectionRecord->fieldLayoutId = $fieldLayout->id;

                    // Proceed if no errors
                    if (!$collectionModel->hasErrors()) {

                        // Save record
                        if ($collectionRecord->save()) {

                            // Transfer record attribute(s) to model
                            $collectionModel->id = $collectionRecord->id;
                            $collectionModel->dateUpdated = $collectionRecord->dateUpdated;
                            $collectionModel->dateCreated = $collectionRecord->dateCreated;

                            // Create field table
                            if (RatingPlugin::getInstance()->getCollectionField()->createByCollection($collectionModel)) {

                                // The 'after' event
                                $afterEvent = new CollectionEvent([
                                    'collection' => $collectionModel
                                ]);

                                // Trigger event
                                $this->trigger(static::EVENT_AFTER_CREATE, $afterEvent);

                                // Green light?
                                if ($afterEvent->isValid) {

                                    // Commit db transaction
                                    $transaction->commit();

                                    return true;

                                }

                            }

                        } else {

                            // Transfer errors
                            $collectionModel->addErrors($collectionRecord->getErrors());

                        }

                    } else {

                        $collectionModel->addError('fieldLayoutId',
                            Craft::t('rating', 'Invalid field layout.'));

                    }

                }

            }

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }


    /*******************************************
     * UPDATE
     *******************************************/

    /**
     * Update exiting rating collection.
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function update(CollectionModel $collectionModel)
    {

        // Ensure we're updating a record
        if (!$collectionModel->id) {

            return $this->create($collectionModel);

        }

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new CollectionEvent([
                'collection' => $collectionModel
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_UPDATE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // Get record (or throw an Exception)
                $collectionRecord = $this->getRecord($collectionModel->id);

                // Create an existing collection model
                $existingCollectionModel = $this->newModel($collectionRecord);

                // Transfer attributes to record
                $this->transferFromModelToRecord($collectionModel, $collectionRecord);

                // Delete field layout
                Craft::$app->getFields()->deleteLayoutById($collectionRecord->fieldLayoutId);

                // Get layout
                $fieldLayout = $collectionModel->getFieldLayout();

                // Save layout
                if (Craft::$app->getFields()->saveLayout($fieldLayout)) {

                    // Transfer layout id
                    $collectionModel->fieldLayoutId = $collectionRecord->fieldLayoutId = $fieldLayout->id;

                    // Proceed if no errors
                    if (!$collectionModel->hasErrors()) {

                        // Save record
                        if ($collectionRecord->save()) {

                            // Transfer record attribute(s) to element
                            $collectionModel->dateUpdated = $collectionRecord->dateUpdated;

                            // Update field table
                            if (RatingPlugin::getInstance()->getCollectionField()->updateByCollection($collectionModel,
                                $existingCollectionModel)
                            ) {

                                // The 'after' event
                                $afterEvent = new CollectionEvent([
                                    'collection' => $collectionModel,
                                    'existingCollection' => $existingCollectionModel
                                ]);

                                // Trigger event
                                $this->trigger(static::EVENT_AFTER_UPDATE, $afterEvent);

                                // Green light?
                                if ($afterEvent->isValid) {

                                    // Commit db transaction
                                    $transaction->commit();

                                    return true;

                                }

                            }

                        } else {

                            // Transfer errors
                            $collectionModel->addErrors($collectionRecord->getErrors());

                        }

                    }

                }

            }

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }


    /*******************************************
     * DELETE
     *******************************************/

    /**
     * Delete an rating collection
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function delete(CollectionModel $collectionModel)
    {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new CollectionEvent([
                'collection' => $collectionModel
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_DELETE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // Get record (or throw an Exception)
                $collectionRecord = $this->getRecord($collectionModel->id);

                // Delete record
                if ($collectionRecord->delete()) {

                    // Delete field table
                    if (RatingPlugin::getInstance()->getCollectionField()->deleteByCollection($collectionModel)) {

                        // The 'after' event
                        $afterEvent = new CollectionEvent([
                            'collection' => $collectionModel
                        ]);

                        // Trigger event
                        $this->trigger(static::EVENT_AFTER_DELETE, $afterEvent);

                        // Green light?
                        if ($afterEvent->isValid) {

                            // Commit db transaction
                            $transaction->commit();

                            return true;

                        }

                    }

                } else {

                    // Transfer errors
                    $collectionModel->addErrors($collectionRecord->getErrors());

                }

            }

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }

    /**
     * Transfer attributes from the model to the record
     *
     * @param CollectionModel $collectionModel
     * @param CollectionRecord $collectionRecord
     * @throws Exception
     */
    private function transferFromModelToRecord(CollectionModel $collectionModel, CollectionRecord $collectionRecord)
    {

        // Transfer attribute(s) to record
        $collectionRecord->name = $collectionModel->name;
        $collectionRecord->handle = $collectionModel->handle;
        $collectionRecord->elementType = $collectionModel->elementType;

    }

    /*******************************************
     * EXCEPTIONS
     *******************************************/

    /**
     * @throws Exception
     */
    protected function notFoundException()
    {

        throw new Exception(Craft::t(
            'rating',
            'Rating collection does not exist.'
        ));

    }

    /**
     * @param null $id
     * @throws Exception
     */
    protected function notFoundByIdException($id = null)
    {

        throw new Exception(Craft::t(
            'rating',
            'Rating collection does not exist with the id "{id}".',
            ['id' => $id]
        ));

    }

    /**
     * @param null $handle
     * @throws Exception
     */
    protected function notFoundByHandleException($handle = null)
    {

        throw new Exception(Craft::t(
            'rating',
            'Rating collection does not exist with the handle "{handle}".',
            ['handle' => $handle]
        ));

    }

}
