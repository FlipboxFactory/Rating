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
use craft\app\helpers\DbHelper;
use craft\plugins\rating\events\Field as FieldEvent;
use craft\plugins\rating\models\Field as FieldModel;
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\records\Field as FieldRecord;
use craft\plugins\rating\records\Rating as RatingRecord;
use yii\base\Component;

class Field extends Component
{

    /** Common model interactions */
    use traits\Model;

    /** Prefix used for rating field columns */
    const FIELD_PREFIX = 'rating_';

    /**
     * @event FieldEvent The event that is triggered before a field is created.
     *
     * You may set [[FieldEvent::isValid]] to `false` to prevent the field from getting created.
     */
    const EVENT_BEFORE_CREATE = 'beforeCreate';

    /**
     * @event FieldEvent The event that is triggered after a field is created.
     *
     * * You may set [[FieldEvent::isValid]] to `false` to prevent the field from getting created.
     */
    const EVENT_AFTER_CREATE = 'afterCreate';

    /**
     * @event FieldEvent The event that is triggered before a field is updated.
     *
     * You may set [[FieldEvent::isValid]] to `false` to prevent the field from getting updated.
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';

    /**
     * @event FieldEvent The event that is triggered after a field is updated.
     *
     * * You may set [[FieldEvent::isValid]] to `false` to prevent the field from getting updated.
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';

    /**
     * @event FieldEvent The event that is triggered before a field is deleted.
     *
     * You may set [[FieldEvent::isValid]] to `false` to prevent the field from getting deleted.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @event FieldEvent The event that is triggered after a field is deleted.
     *
     * * You may set [[FieldEvent::isValid]] to `false` to prevent the field from getting deleted.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';


    /*******************************************
     * RECORDS
     *******************************************/

    /**
     * Get a record (when you explicitly require one)
     *
     * @param $condition
     * @return FieldRecord
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
     * @return array|FieldRecord[]
     */
    protected function findAllRecords($condition = null)
    {
        if (is_null($condition)) {
            return FieldRecord::find()->all();
        }

        return FieldRecord::findAll($condition);
    }

    /**
     * Find a record
     *
     * @param $condition
     * @return null|FieldRecord
     */
    protected function findRecord($condition)
    {
        return FieldRecord::findOne($condition);
    }


    /*******************************************
     * MODELS
     *******************************************/

    /**
     * @param $config
     * @return FieldModel
     */
    protected function newModel($config)
    {
        return FieldModel::create($config);
    }


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * Save a new or existing field.
     *
     * @param FieldModel $fieldModel
     * @return bool
     * @throws \Exception
     */
    public function save(FieldModel $fieldModel)
    {

        if (!$fieldModel->id) {

            return $this->create($fieldModel);

        }

        return $this->update($fieldModel);

    }


    /*******************************************
     * CREATE
     *******************************************/

    /**
     * Create new field.
     *
     * @param FieldModel $fieldModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function create(FieldModel $fieldModel)
    {

        // Ensure we're creating a record
        if ($fieldModel->id) {

            return $this->update($fieldModel);

        }

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new FieldEvent([
                'field' => $fieldModel
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_CREATE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // New record
                $fieldRecord = new FieldRecord();

                // Transfer attributes
                $this->transferFromElementToRecord($fieldModel, $fieldRecord);

                // Validate record
                if (!$fieldRecord->validate()) {

                    // Transfer errors
                    $fieldModel->addErrors($fieldRecord->getErrors());

                }

                // Proceed if no errors
                if (!$fieldModel->hasErrors()) {

                    // Save record (without validation)
                    $fieldRecord->save(false);

                    // Transfer record attribute(s) to model
                    $fieldModel->id = $fieldRecord->id;
                    $fieldModel->dateUpdated = $fieldRecord->dateUpdated;
                    $fieldModel->dateCreated = $fieldRecord->dateCreated;

                    // Save field to table
                    if ($this->saveFieldToTable($fieldModel)) {

                        // Update rating field version
                        RatingPlugin::getInstance()->getSetting()->updateFieldVersion();

                        // The 'after' event
                        $afterEvent = new FieldEvent([
                            'field' => $fieldModel
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

                }

            }

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // roll back all db actions (fail)
        if ($transaction !== null) {

            $transaction->rollback();

        }

        return false;

    }

    /*******************************************
     * UPDATE
     *******************************************/

    /**
     * Update exiting field.
     *
     * @param FieldModel $fieldModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function update(FieldModel $fieldModel)
    {

        // Ensure we're updating a record
        if (!$fieldModel->id) {

            return $this->create($fieldModel);

        }

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new FieldEvent([
                'field' => $fieldModel
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_UPDATE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // Get record (or throw an Exception)
                $fieldRecord = $this->getRecord($fieldModel->id);

                // Get old field model
                $oldFieldModel = FieldModel::create($fieldRecord);

                // Transfer attributes
                $this->transferFromElementToRecord($fieldModel, $fieldRecord);

                // Validate record
                if (!$fieldRecord->validate()) {

                    // Transfer errors
                    $fieldModel->addErrors($fieldRecord->getErrors());

                }

                // Proceed if no errors
                if (!$fieldModel->hasErrors()) {

                    // Save record (without validation)
                    $fieldRecord->save(false);

                    // Transfer record attribute(s) to element
                    $fieldModel->dateUpdated = $fieldRecord->dateUpdated;

                    // Save field to table
                    if ($this->saveFieldToTable($fieldModel, $oldFieldModel)) {

                        // Update rating field version
                        RatingPlugin::getInstance()->getSetting()->updateFieldVersion();

                        // The 'after' event
                        $afterEvent = new FieldEvent([
                            'field' => $fieldModel
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

                }

            }

        } catch (\Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // roll back all db actions (fail)
        if ($transaction !== null) {

            $transaction->rollback();

        }

        return false;

    }

    /*******************************************
     * DELETE
     *******************************************/

    /**
     * Delete an rating field
     *
     * @param FieldModel $fieldModel
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function delete(FieldModel $fieldModel)
    {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new FieldEvent([
                'field' => $fieldModel
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_DELETE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // Get record (or throw an Exception)
                $fieldRecord = $this->getRecord($fieldModel->id);

                // Delete record
                if ($fieldRecord->delete()) {

                    // Remove field from table
                    if ($this->deleteFieldFromTable($fieldModel)) {

                        // Update rating field version
                        RatingPlugin::getInstance()->getSetting()->updateFieldVersion();

                        // The 'after' event
                        $afterEvent = new FieldEvent([
                            'field' => $fieldModel
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
                    $fieldModel->addErrors($fieldRecord->getErrors());

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

//    /**
//     * Assemble an array of field models from post data
//     *
//     * @param string $fields
//     * @return FieldModel[]
//     */
//    public function assembleFieldsFromPost($fields = 'ratings')
//    {
//        if (is_string($fields)) {
//            $fields = Craft::$app->getRequest()->getBodyParam($fields, []);
//        }
//
//        if (!isset($this->_rawFields)) {
//            $this->_rawFields = [];
//        }
//
//        // Get all fields (indexed by handle)
//        $allFields = $this->findAll('handle');
//
//        // Empty array of models
//        $fieldModels = [];
//
//        // Iterate over all submitted fields
//        foreach ($fields as $fieldHandle => $fieldValue) {
//
//            // Field must exist
//            if (array_key_exists($fieldHandle, $allFields)) {
//
//                /** @var FieldModel $fieldModel */
//                $fieldModel = $allFields[$fieldHandle]->copy();
//
//                $fieldModel->setValue($fieldValue);
//
//                $fieldModels[] = $fieldModel;
//
//            }
//
//        }
//
//        return $fieldModels;
//
//    }

    public function getColumnName(FieldModel $fieldModel)
    {
        return static::FIELD_PREFIX . $fieldModel->handle;
    }

    /**
     * Remove field column from rating table
     *
     * @param FieldModel $fieldModel
     * @return bool
     * @throws \yii\db\Exception
     */
    private function deleteFieldFromTable(FieldModel $fieldModel)
    {

        // Get column name
        $columnName = $this->getColumnName($fieldModel);

        // Make sure column exists
        if (Craft::$app->getDb()->columnExists(RatingRecord::tableName(), $columnName)) {

            // Drop column
            Craft::$app->getDb()->createCommand()->dropColumn(RatingRecord::tableName(), $columnName)->execute();

        }

        return true;

    }

    /**
     * Save field column to rating table
     *
     * @param FieldModel $fieldModel
     * @param FieldModel $oldFieldModel
     * @param bool $updateOldFieldOnly
     * @return bool
     * @throws \yii\db\Exception
     */
    private function saveFieldToTable(
        FieldModel $fieldModel,
        FieldModel $oldFieldModel = null,
        $updateOldFieldOnly = false
    ) {

        $columnType = DbHelper::getNumericalColumnType($fieldModel->min, $fieldModel->max,
            $fieldModel->precision);

        $columnName = $this->getColumnName($fieldModel);

        // If an old field is provided, we are only renaming
        if (!is_null($oldFieldModel)) {

            $oldColumnName = $this->getColumnName($oldFieldModel);

            if (Craft::$app->getDb()->columnExists(RatingRecord::tableName(), $oldColumnName)) {

                Craft::$app->getDb()->createCommand()->alterColumn(
                    RatingRecord::tableName(),
                    $oldColumnName,
                    $columnType,
                    $columnName
                )->execute();

                return true;

            }

            // Halt if old field was not found
            if ($updateOldFieldOnly) {
                return true;
            }

        }

        if (Craft::$app->getDb()->columnExists(RatingRecord::tableName(), $columnName)) {

            // Alter column
            Craft::$app->getDb()->createCommand()->alterColumn(
                RatingRecord::tableName(),
                $columnName,
                $columnType
            )->execute();

        } else {

            // Add column
            Craft::$app->getDb()->createCommand()->addColumnBefore(
                RatingRecord::tableName(),
                $columnName,
                $columnType,
                'dateCreated'
            )->execute();

        }

        return true;

    }

    /**
     * Transfer attributes from the field element to the field record
     *
     * @param FieldModel $fieldModel
     * @param FieldRecord $fieldRecord
     * @throws Exception
     */
    private function transferFromElementToRecord(FieldModel $fieldModel, FieldRecord $fieldRecord)
    {

        // Name
        if (!is_null($fieldModel->name)) {
            $fieldRecord->name = $fieldModel->name;
        }

        // Handle
        if (!is_null($fieldModel->handle)) {
            $fieldRecord->handle = $fieldModel->handle;
        }

        // Min
        if (!is_null($fieldModel->min)) {
            $fieldRecord->min = $fieldModel->min;
        }

        // Max
        if (!is_null($fieldModel->max)) {
            $fieldRecord->max = $fieldModel->max;
        }

        // Increment
        if (!is_null($fieldModel->increment)) {
            $fieldRecord->increment = $fieldModel->increment;
        }

        // Precision
        if (!is_null($fieldModel->precision)) {
            $fieldRecord->precision = $fieldModel->precision;
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
            'Rating field does not exist.'
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
            'Rating field does not exist with the id "{id}".',
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
            'Rating field does not exist with the handle "{handle}".',
            ['handle' => $handle]
        ));

    }

}
