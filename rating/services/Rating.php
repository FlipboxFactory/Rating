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
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\elements\Rating as RatingElement;
use craft\plugins\rating\events\rating\Delete as RatingDeleteEvent;
use craft\plugins\rating\events\rating\Event as RatingEvent;
use craft\plugins\rating\events\rating\Status as RatingStatusEvent;
use craft\plugins\rating\models\Settings as RatingSettings;
use craft\plugins\rating\records\Rating as RatingRecord;
use yii\base\Component;

class Rating extends Component
{

    /** Common element interactions */
    use traits\Element;

    /**
     * @event RatingEvent The event that is triggered before a rating is created.
     *
     * You may set [[RatingEvent::isValid]] to `false` to prevent the rating from getting created.
     */
    const EVENT_BEFORE_CREATE = 'beforeCreate';

    /**
     * @event RatingEvent The event that is triggered after a rating is created.
     *
     * * You may set [[RatingEvent::isValid]] to `false` to prevent the rating from getting created.
     */
    const EVENT_AFTER_CREATE = 'afterCreate';

    /**
     * @event RatingEvent The event that is triggered before a rating is updated.
     *
     * You may set [[RatingEvent::isValid]] to `false` to prevent the rating from getting updated.
     */
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';

    /**
     * @event RatingEvent The event that is triggered after a rating is updated.
     *
     * * You may set [[RatingEvent::isValid]] to `false` to prevent the rating from getting updated.
     */
    const EVENT_AFTER_UPDATE = 'afterUpdate';

    /**
     * @event RatingDeleteEvent The event that is triggered before a rating is deleted.
     *
     * You may set [[RatingDeleteEvent::isValid]] to `false` to prevent the rating from getting deleted.
     */
    const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @event RatingDeleteEvent The event that is triggered after a rating is deleted.
     *
     * * You may set [[RatingDeleteEvent::isValid]] to `false` to prevent the rating from getting deleted.
     */
    const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * @event RatingStatusEvent The event that is triggered before a rating has a custom status change.
     *
     * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating changing the status.
     */
    const EVENT_BEFORE_STATUS_CHANGE = 'beforeStatusChange';

    /**
     * @event RatingStatusEvent The event that is triggered after a rating has a custom status change.
     *
     * * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating changing the status.
     */
    const EVENT_AFTER_STATUS_CHANGE = 'afterStatusChange';

    /**
     * @event RatingStatusEvent The event that is triggered before a rating is activated.
     *
     * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating from getting set to active.
     */
    const EVENT_BEFORE_ACTIVATE = 'beforeActivate';

    /**
     * @event RatingStatusEvent The event that is triggered after a rating is activated.
     *
     * * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating from getting set to active.
     */
    const EVENT_AFTER_ACTIVATE = 'afterActivate';

    /**
     * @event RatingStatusEvent The event that is triggered before a rating is activated.
     *
     * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating from getting set to pending.
     */
    const EVENT_BEFORE_PENDING = 'beforePending';

    /**
     * @event RatingStatusEvent The event that is triggered after a rating is set to pending.
     *
     * * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating from getting set to pending.
     */
    const EVENT_AFTER_PENDING = 'afterPending';

    /**
     * @event RatingStatusEvent The event that is triggered before a rating is disabled.
     *
     * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating from getting set to disabled.
     */
    const EVENT_BEFORE_DISABLE = 'beforeDisable';

    /**
     * @event RatingStatusEvent The event that is triggered after a rating is set to disabled.
     *
     * * You may set [[RatingStatusEvent::isValid]] to `false` to prevent the rating from getting set to disabled.
     */
    const EVENT_AFTER_DISABLE = 'afterDisable';


    /*******************************************
     * SAVE
     *******************************************/

    /**
     * Save a new or existing rating.
     *
     * @param RatingElement $ratingElement
     * @return bool
     * @throws \Exception
     */
    public function save(RatingElement $ratingElement)
    {

        if (!$ratingElement->id) {

            return $this->create($ratingElement);

        }

        return $this->update($ratingElement);

    }


    /*******************************************
     * CREATE
     *******************************************/

    /**
     * Create a new rating.
     *
     * @param RatingElement $ratingElement
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function create(RatingElement $ratingElement)
    {

        // Ensure we're creating a record
        if ($ratingElement->id) {

            return $this->update($ratingElement);

        }

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new RatingEvent([
                'rating' => $ratingElement
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_CREATE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // New record
                $ratingRecord = new RatingRecord();

                // Transfer attributes to record
                $this->transferFromElementToRecord($ratingElement, $ratingRecord);

                // Validate record
                if (!$ratingRecord->validate()) {

                    // Transfer errors
                    $ratingElement->addErrors($ratingRecord->getErrors());

                }

                // Validate content
                if ($ratingElement->hasContent() && !Craft::$app->getContent()->validateContent($ratingElement)) {

                    // Transfer errors
                    $ratingElement->addErrors($ratingElement->getContent()->getErrors());

                }

                // Proceed if no errors
                if (!$ratingElement->hasErrors()) {

                    // Save the element
                    if (Craft::$app->getElements()->saveElement(
                        $ratingElement,
                        false
                    )
                    ) {

                        // Transfer id for new records
                        $ratingRecord->id = $ratingElement->id;

                        // Save record
                        if ($ratingRecord->save()) {

                            // Transfer record attribute(s) to element
                            $ratingElement->dateUpdated = $ratingRecord->dateUpdated;
                            $ratingElement->dateCreated = $ratingRecord->dateCreated;

                            // The 'after' event
                            $afterEvent = new RatingEvent([
                                'rating' => $ratingElement
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
     * Update an existing rating.
     *
     * @param RatingElement $ratingElement
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function update(RatingElement $ratingElement)
    {

        // Ensure we're updating a record
        if (!$ratingElement->id) {

            return $this->create($ratingElement);

        }

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The 'before' event
            $beforeEvent = new RatingEvent([
                'rating' => $ratingElement
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_UPDATE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // Status change flag
                $doStatusChange = false;

                // Get record (or throw an Exception)
                $ratingRecord = $this->getRecord($ratingElement->id);

                // Look for status change
                if ($ratingRecord->status !== $ratingElement->getStatus()) {

                    $doStatusChange = $ratingElement->getStatus();

                }

                // Transfer attributes to record
                $this->transferFromElementToRecord($ratingElement, $ratingRecord);


                // Validate
                if (!$ratingRecord->validate()) {

                    // Transfer errors
                    $ratingElement->addErrors($ratingRecord->getErrors());

                }

                // Validate content
                if ($ratingElement->hasContent() && !Craft::$app->getContent()->validateContent($ratingElement)) {

                    // Transfer errors
                    $ratingElement->addErrors($ratingElement->getContent()->getErrors());

                }

                // Proceed if no errors
                if (!$ratingElement->hasErrors()) {

                    // Save the element (without validation)
                    if (Craft::$app->getElements()->saveElement(
                        $ratingElement,
                        false
                    )
                    ) {

                        // Save record (without validation)
                        $success = $ratingRecord->save(false);

                        // Transfer record attribute(s) to element
                        $ratingElement->dateUpdated = $ratingRecord->dateUpdated;

                        // Change status
                        if ($doStatusChange) {
                            $success = $this->changeStatus($ratingElement, $doStatusChange);
                        }

                        // Continue if successful
                        if ($success) {

                            // The 'after' event
                            $afterEvent = new RatingEvent([
                                'rating' => $ratingElement
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
     * Delete a rating.
     *
     * @param RatingElement $ratingElement
     * @param RatingElement $transferTo
     * @return bool
     * @throws Exception
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function delete(RatingElement $ratingElement, RatingElement $transferTo = null)
    {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // The before event
            $beforeEvent = new RatingDeleteEvent([
                'rating' => $ratingElement,
                'transferTo' => $transferTo
            ]);

            // Trigger event
            $this->trigger(static::EVENT_BEFORE_DELETE, $beforeEvent);

            // Green light?
            if ($beforeEvent->isValid) {

                // todo - allow plugins to register transfer rating hook

                // Delete element
                if (Craft::$app->getElements()->deleteElementById($ratingElement->id)) {

                    // The 'after' event
                    $afterEvent = new RatingDeleteEvent([
                        'rating' => $ratingElement,
                        'transferTo' => $transferTo
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

            }

        } catch (Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }


    /*******************************************
     * STATUS
     *******************************************/

    /**
     *
     * @param RatingElement $ratingElement
     * @param $status
     * @param $beforeEventName
     * @param $afterEventName
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function saveStatus(
        RatingElement $ratingElement,
        $status,
        $beforeEventName = null,
        $afterEventName = null
    ) {

        // Db transaction
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            // Trigger before event?
            if (!is_null($beforeEventName)) {

                // The before event
                $beforeEvent = new RatingStatusEvent([
                    'rating' => $ratingElement,
                    'status' => $status
                ]);

                // Trigger event
                $this->trigger($beforeEventName, $beforeEvent);

            }

            // Green light?
            if (empty($beforeEvent) || $beforeEvent->isValid) {

                // Get record (or throw an Exception)
                $ratingRecord = $this->getRecord($ratingElement->id);

                // Set status
                $ratingRecord->status = $status;

                // Validate record (status only)
                if (!$ratingRecord->validate('status')) {

                    // Transfer errors
                    $ratingElement->addErrors($ratingRecord->getErrors());

                }

                // Save the status only
                if (Craft::$app->getDb()->createCommand()->update(
                    RatingRecord::tableName(),
                    array('status' => $status),
                    array('id' => $ratingElement->id)
                )->execute()
                ) {

                    // Transfer record attribute(s) to element
                    $ratingElement->status = $ratingRecord->status;

                    // Trigger after event?
                    if (!is_null($afterEventName)) {

                        // The after event
                        $afterEvent = new RatingStatusEvent([
                            'rating' => $ratingElement,
                            'status' => $status
                        ]);

                        // Trigger event
                        $this->trigger($afterEventName, $afterEvent);

                    }

                    // Green light?
                    if (empty($afterEvent) || $afterEvent->isValid) {

                        // Commit db transaction
                        $transaction->commit();

                        return true;

                    }

                }

            }

        } catch (Exception $e) {

            // roll back all db actions (fail)
            $transaction->rollback();

            throw $e;

        }

        // roll back all db actions (fail)
        $transaction->rollback();

        return false;

    }

    /**
     * @param RatingElement $ratingElement
     * @param $status
     * @return bool
     */
    public function changeStatus(RatingElement $ratingElement, $status)
    {

        // Do status action
        switch ($status) {

            case RatingSettings::STATUS_ACTIVE:
                return $this->activate($ratingElement);

            case RatingSettings::STATUS_PENDING:
                return $this->pending($ratingElement);

            case RatingSettings::STATUS_DISABLE:
                return $this->disable($ratingElement);

        }

        return $this->saveStatus(
            $ratingElement,
            $status,
            static::EVENT_BEFORE_STATUS_CHANGE,
            static::EVENT_AFTER_STATUS_CHANGE
        );

    }

    /*******************************************
     * ACTIVATE
     *******************************************/

    /**
     * @param RatingElement $ratingElement
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function activate(RatingElement $ratingElement)
    {

        return $this->saveStatus(
            $ratingElement,
            RatingSettings::STATUS_ACTIVE,
            static::EVENT_BEFORE_ACTIVATE,
            static::EVENT_AFTER_ACTIVATE
        );

    }


    /*******************************************
     * DISABLE
     *******************************************/

    /**
     * @param RatingElement $ratingElement
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function disable(RatingElement $ratingElement)
    {

        return $this->saveStatus(
            $ratingElement,
            RatingSettings::STATUS_DISABLE,
            static::EVENT_BEFORE_DISABLE,
            static::EVENT_AFTER_DISABLE
        );

    }


    /*******************************************
     * PENDING
     *******************************************/

    /**
     * @param RatingElement $ratingElement
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function pending(RatingElement $ratingElement)
    {

        return $this->saveStatus(
            $ratingElement,
            RatingSettings::STATUS_PENDING,
            static::EVENT_BEFORE_PENDING,
            static::EVENT_AFTER_PENDING
        );

    }


    /**
     * Transfer attributes from the rating element to the rating record
     *
     * @param RatingElement $ratingElement
     * @param RatingRecord $ratingRecord
     * @throws Exception
     */
    private function transferFromElementToRecord(RatingElement $ratingElement, RatingRecord $ratingRecord)
    {

        // Collection
        $ratingRecord->collectionId = $ratingElement->getCollection()->id;

        // Element
        if ($ratingElement->hasElement()) {
            $ratingRecord->elementId = $ratingElement->getElement()->id;
        }

        // Owner
        if ($ratingElement->hasOwner()) {
            $ratingRecord->ownerId = $ratingElement->getOwner()->id;
        }

        // Name
        if (!is_null($ratingElement->name)) {
            $ratingRecord->name = $ratingElement->name;
        }

        // Email
        if (!is_null($ratingElement->email)) {
            $ratingRecord->email = $ratingElement->email;
        }

        // Status (set on create only)
        if ($status = $ratingElement->getStatus() && $ratingRecord->getIsNewRecord()) {
            $ratingRecord->status = $status;
        }

        // Rating Fields
        foreach ($ratingElement->getRatingFields() as $ratingField) {

            $columnName = RatingPlugin::getInstance()->getField()->getColumnName($ratingField);

            // Verify column exists
            if (Craft::$app->getDb()->columnExists(RatingRecord::tableName(), $columnName)) {

                // Set column value
                $ratingRecord->{$columnName} = DbHelper::prepareValueForDb($ratingField->getValue());

            }

        }

    }


    /*******************************************
     * RECORDS
     *******************************************/

    /**
     * @param $id
     * @return null|RatingRecord
     */
    private function findRecord($id)
    {
        return RatingRecord::findOne($id);
    }

    /**
     * @param $id
     * @return RatingRecord
     * @throws Exception
     */
    private function getRecord($id)
    {

        if (!$record = $this->findRecord($id)) {

            if (is_numeric($id)) {

                $this->notFoundByIdException($id);

            }

            $this->notFoundException();

        }

        return $record;

    }

    /*******************************************
     * ELEMENT CLASS
     *******************************************/

    /**
     * @return string
     */
    protected function getElementClassName()
    {
        return RatingElement::className();
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
            'Rating does not exist.'
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
            'Rating does not exist with the id "{id}".',
            ['id' => $id]
        ));

    }

    /**
     * @param null $string
     * @throws Exception
     */
    protected function notFoundByStringException($string = null)
    {

        throw new Exception(Craft::t(
            'rating',
            'Rating does not exist with the string "{string}".',
            ['string' => $string]
        ));

    }

}
