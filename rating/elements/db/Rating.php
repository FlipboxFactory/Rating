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

namespace craft\plugins\rating\elements\db;

use Craft;
use craft\app\elements\db\ElementQuery;
use craft\app\helpers\ArrayHelper;
use craft\app\helpers\DbHelper;
use craft\plugins\rating\models\Settings as SettingsModel;
use craft\plugins\rating\models\Field as FieldModel;
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\services\Field;
use craft\app\events\PopulateElementEvent;
use craft\app\base\FieldInterface;
use craft\app\base\Element;
use craft\app\base\ElementInterface;

class Rating extends ElementQuery
{

    /** Rating field handles */
    use traits\Rating;

    /** Common Element interactions */
    use traits\Element;

    /** Common Owner interactions */
    use traits\Owner;

    /** Common Collection interactions */
    use traits\Collection;

    /**
     * The plain text table name
     */
    const TABLE_NAME = 'ratings';

    /**
     * @var integer|integer[] The volume ID(s) that the resulting assets must be in.
     */
    public $id;

    /**
     * @var string Publisher name
     */
    public $name;

    /**
     * @var string Publisher email
     */
    public $email;

    /**
     * @inheritdoc
     */
    public $status = SettingsModel::STATUS_ACTIVE;

    /**
     * @var FieldModel[] The rating fields that may be involved in this query.
     */
    public $customRatingFields = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            ['customRatingFields' => behaviors\Rating::className()]
        );
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare()
    {

        $this->joinElementTable(static::TABLE_NAME);

        $this->query->select(
            [
                static::TABLE_NAME . '.id',
                static::TABLE_NAME . '.collectionId',
                static::TABLE_NAME . '.elementId',
                static::TABLE_NAME . '.ownerId',
                static::TABLE_NAME . '.name',
                static::TABLE_NAME . '.email',
                static::TABLE_NAME . '.status'
            ]
        );

        if ($this->id)
        {
            $this->subQuery->andWhere(
                DbHelper::parseParam(
                    static::TABLE_NAME . '.id',
                    $this->id,
                    $this->subQuery->params
                )
            );
        }

        if ($this->collectionId)
        {
            $this->subQuery->andWhere(
                DbHelper::parseParam(
                    static::TABLE_NAME . '.collectionId',
                    $this->collectionId,
                    $this->subQuery->params
                )
            );

        }

        if ($this->elementId)
        {
            $this->subQuery->andWhere(
                DbHelper::parseParam(
                    static::TABLE_NAME . '.elementId',
                    $this->elementId,
                    $this->subQuery->params
                )
            );
        }

        if ($this->ownerId)
        {
            $this->subQuery->andWhere(
                DbHelper::parseParam(
                    static::TABLE_NAME . '.ownerId',
                    $this->ownerId,
                    $this->subQuery->params
                )
            );
        }

        if ($this->name)
        {
            $this->subQuery->andWhere(
                DbHelper::parseParam(
                    static::TABLE_NAME . '.name',
                    $this->name,
                    $this->subQuery->params
                )
            );
        }

        if ($this->email)
        {
            $this->subQuery->andWhere(
                DbHelper::parseParam(
                    static::TABLE_NAME . '.email',
                    $this->email,
                    $this->subQuery->params
                )
            );
        }

        if ($this->status)
        {
            $this->subQuery->andWhere(
                DbHelper::parseParam(
                    static::TABLE_NAME . '.status',
                    $this->status,
                    $this->subQuery->params
                )
            );
        }

        // todo - we may want to only get rating fields that are to be used...
        $this->customRatingFields = RatingPlugin::getInstance()->getField()->findAll();

        if (is_array($this->customRatingFields)) {

            foreach ($this->customRatingFields as $fieldModel) {

                $this->query->addSelect([
                    static::TABLE_NAME . '.' . RatingPlugin::getInstance()->getField()->getColumnName($fieldModel)
                ]);

            }

        }

        return parent::beforePrepare();

    }


    /**
     * @inheritdoc
     */
    public function ratingFields()
    {
        $fields = array_unique(array_merge(
            array_keys(Craft::getObjectVars($this)),
            array_keys(Craft::getObjectVars($this->getBehavior('customRatingFields')))
        ));
        $fields = array_combine($fields, $fields);
        unset($fields['query'], $fields['subQuery']);

        return $fields;
    }

    /** THIS IS A COPY FROM THE BASE ELEMENT QUERY BECAUSE WE NEED ACCESS TO _createElements */
    public function populate($rows)
    {
        if (empty($rows)) {
            return [];
        }

        $elements = $this->_copied__createElements($rows);

        return $elements;
    }

    /** THIS IS A COPY FROM THE BASE ELEMENT QUERY BECAUSE WE NEED ACCESS TO _createElement */
    private function _copied__createElements($rows)
    {
        $elements = [];

        if ($this->asArray) {
            if ($this->indexBy === null) {
                return $rows;
            }

            foreach ($rows as $row) {
                if (is_string($this->indexBy)) {
                    $key = $row[$this->indexBy];
                } else {
                    $key = call_user_func($this->indexBy, $row);
                }

                $elements[$key] = $row;
            }
        } else {
            $lastElement = null;

            foreach ($rows as $row) {

                $element = $this->_createElement($row);

                if ($element === false) {
                    continue;
                }

                // Add it to the elements array
                if ($this->indexBy === null) {
                    $elements[] = $element;
                } else {
                    if (is_string($this->indexBy)) {
                        $key = $element->{$this->indexBy};
                    } else {
                        $key = call_user_func($this->indexBy, $element);
                    }

                    $elements[$key] = $element;
                }

                // setNext() / setPrev()
                if ($lastElement) {
                    $lastElement->setNext($element);
                    $element->setPrev($lastElement);
                } else {
                    $element->setPrev(false);
                }

                $lastElement = $element;
            }

            $lastElement->setNext(false);
        }

        return $elements;
    }

    /** THIS IS BASICALLY A COPY FROM THE BASE ELEMENT QUERY...IT ONLY NEEDS THE CUSTOM RATING FIELDS WHICH ARE MARKED BELOW */
    private function _createElement($row)
    {
        // Do we have a placeholder for this element?
        $element = Craft::$app->getElements()->getPlaceholderElement($row['id'], $this->locale);

        if ($element !== null) {
            return $element;
        }

        /** @var Element $class */
        $class = $this->elementType;

        // Instantiate the element
        $row['locale'] = $this->locale;

        if ($this->structureId) {
            $row['structureId'] = $this->structureId;
        }

        /** @var ElementInterface|Element $element */
        $element = $class::create($row);

        // Verify that an element was returned
        if (!$element || !($element instanceof ElementInterface)) {
            return false;
        }

        // Set the custom field values
        if ($class::hasContent() && $this->contentTable) {
            // Separate the content values from the main element attributes
            $fieldValues = [];

            if ($this->customFields) {
                foreach ($this->customFields as $field) {
                    if ($field->hasContentColumn()) {
                        // Account for results where multiple fields have the same handle, but from
                        // different columns e.g. two Matrix block types that each have a field with the
                        // same handle
                        $colName = $this->_getFieldContentColumnName($field);

                        if (!isset($fieldValues[$field->handle]) || (empty($fieldValues[$field->handle]) && !empty($row[$colName]))) {
                            $fieldValues[$field->handle] = $row[$colName];
                        }
                    }
                }
            }

            $element->setFieldValues($fieldValues);
        }

        /**
         * CUSTOM START
         */
        $ratingFieldValues = [];
        if ($this->customRatingFields) {
            foreach ($this->customRatingFields as $field) {

                $colName = RatingPlugin::getInstance()->getField()->getColumnName($field);

                if (!isset($ratingFieldValues[$field->handle]) || (empty($ratingFieldValues[$field->handle]) && !empty($row[$colName]))) {
                    $ratingFieldValues[$field->handle] = $row[$colName];
                }
            }
        }
        $element->setRatingFieldValues($ratingFieldValues);
        /**
         * CUSTOM END
         */

        // Fire an 'afterPopulateElement' event
        $this->trigger(static::EVENT_AFTER_POPULATE_ELEMENT, new PopulateElementEvent([
            'element' => $element,
            'row' => $row
        ]));

        return $element;
    }

    /**
     * Returns a field’s corresponding content column name.
     *
     * @param FieldInterface|Field $field
     *
     * @return string
     */
    private function _getFieldContentColumnName(FieldInterface $field)
    {
        return ($field->columnPrefix ?: 'field_').$field->handle;
    }

}
