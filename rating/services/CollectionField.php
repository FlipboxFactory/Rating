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
use craft\plugins\rating\models\Collection as CollectionModel;
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\records\CollectionField as CollectionFieldRecord;
use yii\base\Component;

class CollectionField extends Component
{

    /**
     * @param $collection
     * @param null $indexBy
     * @return array
     */
    public function getAllByCollection($collection, $indexBy = null)
    {

        // Get collection model
        $collectionModel = RatingPlugin::getInstance()->getCollection()->get($collection);

        // Find collection field records
        $collectionFieldRecords = CollectionFieldRecord::findAll([
            'collectionId' => $collectionModel->id
        ]);

        $fieldModels = [];

        foreach ($collectionFieldRecords as $collectionFieldRecord) {

            $fieldModels[] = RatingPlugin::getInstance()->getField()->get($collectionFieldRecord->fieldId);

        }

        if (!$indexBy) {

            $models = $fieldModels;

        } else {

            $models = [];

            foreach ($fieldModels as $model) {

                $models[$model->$indexBy] = $model;

            }

        }

        return $models;

    }


    /*******************************************
     * RECORDS
     *******************************************/

    /**
     * Get a record (when you explicitly require one)
     *
     * @param $condition
     * @return CollectionFieldRecord
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
     * @return array|CollectionFieldRecord[]
     */
    protected function findAllRecords($condition = null)
    {
        if (is_null($condition)) {
            return CollectionFieldRecord::find()->all();
        }

        return CollectionFieldRecord::findAll($condition);
    }

    /**
     * Find a record
     *
     * @param $condition
     * @return null|CollectionFieldRecord
     */
    protected function findRecord($condition)
    {
        return CollectionFieldRecord::findOne($condition);
    }


    /*******************************************
     * CREATE
     *******************************************/

    /**
     * Save fields associated to a collection.
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function createByCollection(CollectionModel $collectionModel)
    {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // Delete all records from collection field table
            if ($this->deleteAllRecordsByCollection($collectionModel)) {

                // Save records to collection field table
                if ($this->saveRecordsByCollection($collectionModel)) {

                    // Commit db transaction
                    $transaction->commit();

                    return true;

                }

            }

        } catch (\Exception $e) {

            // Roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // Roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }

    /*******************************************
     * UPDATE
     *******************************************/

    /**
     * Update fields associated to a collection.
     *
     * @param CollectionModel $collectionModel
     * @param CollectionModel $existingCollectionModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function updateByCollection(CollectionModel $collectionModel, CollectionModel $existingCollectionModel)
    {

        // Get existing rating fields before any deletions happen on the new model
        $existingCollectionModel->getRatingFields();

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // Delete all records from collection field table
            if ($this->deleteAllRecordsByCollection($collectionModel)) {

                // Save records to collection field table
                if ($this->saveRecordsByCollection($collectionModel)) {

                    // Commit db transaction
                    $transaction->commit();

                    return true;

                }

            }

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // Roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }

    /*******************************************
     * DELETE
     *******************************************/

    /**
     * Delete fields associated to a collection.
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     */
    public function deleteByCollection(CollectionModel $collectionModel)
    {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // Delete all records from collection field table
            if ($this->deleteAllRecordsByCollection($collectionModel)) {

                // Commit db transaction
                $transaction->commit();

                return true;

            }

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // Roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }

    /*******************************************
     * RECORDS BY COLLECTION
     *******************************************/

    /**
     * Delete all records from database for a collection.
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function deleteAllRecordsByCollection(CollectionModel $collectionModel)
    {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            CollectionFieldRecord::deleteAll([
                'collectionId' => $collectionModel->id
            ]);

            // Commit db transaction
            $transaction->commit();

            return true;

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

    }

    /**
     * Save all records to database for a collection.
     *
     * @param CollectionModel $collectionModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    private function saveRecordsByCollection(CollectionModel $collectionModel)
    {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            foreach ($collectionModel->getRatingFields() as $ratingField) {

                $collectionFieldRecord = new CollectionFieldRecord();
                $collectionFieldRecord->collectionId = $collectionModel->id;
                $collectionFieldRecord->fieldId = $ratingField->id;
                $collectionFieldRecord->required = false;

                // Save record
                if (!$collectionFieldRecord->save()) {
                    throw new Exception(Craft::t('rating', 'Unable to save collection field.'));
                }

            }

            // Commit db transaction
            $transaction->commit();

            return true;

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

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
            'Rating collection field does not exist.'
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
            'Rating collection field does not exist with the id "{id}".',
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
            'Rating collection field does not exist with the handle "{handle}".',
            ['handle' => $handle]
        ));

    }


}