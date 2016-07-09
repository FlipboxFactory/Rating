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

namespace craft\plugins\rating\elements;

use Craft;
use craft\app\base\Element;
use craft\app\base\ElementInterface;
use craft\app\elements\db\ElementQueryInterface;
use craft\app\errors\Exception;
use craft\app\helpers\StringHelper;
use craft\app\helpers\UrlHelper;
use craft\app\models\FieldLayout as FieldLayoutModel;
use craft\plugins\rating\elements\db\Rating as RatingQuery;
use craft\plugins\rating\models\Collection as CollectionModel;
use craft\plugins\rating\models\Field as FieldModel;
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\services\Field as FieldService;

class Rating extends Element
{

    /** Rating fields */
    use traits\Rating;

    /** Common Element interactions */
    use traits\Element;

    /** Common Owner interactions */
    use traits\Owner;

    /** Common Collection interactions */
    use traits\Collection;

    /**
     * @var string Publisher name
     */
    public $name;

    /**
     * @var string Publisher email
     */
    public $email;

    /**
     * @var string Status
     */
    public $status;

    private $rawBodyRatingContent;
    private $preparedRatingFields;

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
    public static function displayName()
    {
        return Craft::t('rating', 'Rating');
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasContent()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [
            ['elementId', 'ownerId', 'collectionId'],
            'number',
            'min' => -2147483648,
            'max' => 2147483647,
            'integerOnly' => true
        ];

        return $rules;
    }


    /************************************************************
     * FIELD LAYOUT
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {

        if ($collectionModel = $this->getCollection()) {

            if ($fieldLayout = Craft::$app->getFields()->getLayoutById($collectionModel->fieldLayoutId)) {

                return $fieldLayout;

            }

        }

        return new FieldLayoutModel();

    }


    /************************************************************
     * QUERY
     ************************************************************/

    /**
     * @inheritdoc
     *
     * @return RatingQuery The newly created [[RatingQuery]] instance.
     */
    public static function find()
    {
        return new RatingQuery(get_called_class());
    }


    /************************************************************
     * STATUS
     ************************************************************/

    /**
     * Returns whether this element type can have statuses.
     *
     * @return boolean
     */
    public static function hasStatuses()
    {
        return true;
    }

    /**
     * Returns all of the possible statuses that elements of this type may have.
     *
     * @return array|null
     */
    public static function getStatuses()
    {
        $statusArray = RatingPlugin::getInstance()->getSettings()->getStatuses();

        return array_keys($statusArray);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public static function getElementQueryStatusCondition(ElementQueryInterface $query, $status)
    {
        return "ratings.status = '{$status}'";
    }


    /************************************************************
     * RATING FIELDS
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function getRatingFieldValues($fieldHandles = null, $except = [])
    {
        $values = [];

        foreach ($this->getRatingFields() as $field) {
            if ($fieldHandles === null || in_array($field->handle, $fieldHandles)) {
                $values[$field->handle] = $this->getRatingFieldValue($field->handle);
            }
        }

        foreach ($except as $handle) {
            unset($values[$handle]);
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function setRatingFieldValues($values)
    {
        foreach ($values as $fieldHandle => $value) {
            $this->setRatingFieldValue($fieldHandle, $value);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRatingFieldValue($fieldHandle)
    {
        // Is this the first time this field value has been accessed?
        if (!isset($this->preparedRatingFields[$fieldHandle])) {
            $this->prepareRatingFieldValue($fieldHandle);
        }

        $behavior = $this->getBehavior('customRatingFields');

        return $behavior->$fieldHandle;
    }

    /**
     * @inheritdoc
     */
    public function setRatingFieldValue($fieldHandle, $value)
    {
        $behavior = $this->getBehavior('customRatingFields');
        $behavior->$fieldHandle = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRatingFieldValuesFromBody($values = 'ratings')
    {
        if (is_string($values)) {
            $values = Craft::$app->getRequest()->getBodyParam($values, []);
        }

        foreach ($this->getRatingFields() as $field) {
            // Do we have any body data for this field?
            if (isset($values[$field->handle])) {
                $value = $values[$field->handle];
            } else {
                continue;
            }
            $this->setRatingFieldValue($field->handle, $value);
            $this->setRawBodyValueForRatingField($field->handle, $value);
        }
    }

    /**
     * Sets a rating field’s raw body content.
     *
     * @param string $handle The rating field handle.
     * @param string|array The body rating field value.
     */
    public function setRawBodyValueForRatingField($handle, $value)
    {
        $this->rawBodyRatingContent[$handle] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getRatingContentFromBody()
    {
        if (isset($this->rawBodyRatingContent)) {
            return $this->rawBodyRatingContent;
        } else {
            return [];
        }
    }

    /**
     * Returns each of this element’s rating fields.
     *
     * @return FieldModel[] This element’s rating fields
     */
    public function getRatingFields($indexBy = null)
    {
        $collection = $this->getCollection();

        if ($collection) {
            $ratingFieldModels = $collection->getRatingFields();
        } else {
            $ratingFieldModels = [];
        }

        if (!$indexBy) {

            $fields = $ratingFieldModels;

        } else {

            $fields = [];

            foreach ($ratingFieldModels as $field) {

                $fields[$field->$indexBy] = $field;

            }

        }

        return $fields;

    }

    /**
     * @param $identifier
     * @param bool|false $strict
     * @return FieldModel|null
     * @throws Exception
     */
    public function getRatingField($identifier, $strict = false)
    {

        // Determine index type
        $indexBy = (is_numeric($identifier)) ? 'id' : 'handle';

        // Get all
        $allRatings = $this->getRatingFields($indexBy);

        // look for key in array
        if (array_key_exists($identifier, $allRatings)) {

            return $allRatings[$identifier];

        } else {

            // Throw and exception?
            if ($strict) {

                // Element not found
                throw new Exception(Craft::t(
                    'rating',
                    'Rating field does not exist.'
                ));

            }

        }

        return null;

    }

    /**
     * Prepares a field’s value for use.
     *
     * @param string $fieldHandle The field handle
     *
     * @return void
     * @throws Exception if there is no field with the handle $fieldValue
     */
    protected function prepareRatingFieldValue($fieldHandle)
    {

        $field = $this->getRatingField($fieldHandle);

        $behavior = $this->getBehavior('customRatingFields');
        $behavior->$fieldHandle = $field->prepareValue($behavior->$fieldHandle, $this);
        $this->preparedRatingFields[$fieldHandle] = true;
    }


    /************************************************************
     * ELEMENT ADMIN
     ************************************************************/

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::getCpUrl('rating/' . $this->getCollection()->handle . '/' . $this->id);
    }

    /**
     * @inheritdoc
     */
    public function getUrlFormat()
    {
        return '{collection.handle}/{id}';
    }

    /**
     * @inheritdoc
     */
    public static function getSources($context = null)
    {
        $sources = [
            '*' => [
                'label' => Craft::t('rating', 'All Collections'),
                'hasThumbs' => false
            ]
        ];

        /** @var CollectionModel[] $collections */
        $collections = RatingPlugin::getInstance()->getCollection()->findAll();

        // Iterate through all collections
        foreach ($collections as $collection) {
            $sources['collection:' . $collection->id] = array(
                'label' => $collection->name,
                'criteria' => array('collectionId' => $collection->id)
            );
        }

        // Allow plugins to modify the sources
        Craft::$app->getPlugins()->call('modifyRatingSources',
            [&$sources, $context]);

        return $sources;
    }

    /**
     * @inheritdoc
     */
    public static function getAvailableActions($source = null)
    {

        // Empty action array
        $actions = [];

        // Allow plugins to modify the actions
        Craft::$app->getPlugins()->call(
            'modifyRatingActions',
            [&$actions]
        );


        return $actions;

    }

    /**
     * @inheritdoc
     */
    public static function defineSearchableAttributes()
    {

        // Default attributes
        $attributes = ['title', 'status'];

        // Allow plugins to modify the attributes
        Craft::$app->getPlugins()->call(
            'modifyRatingSearchableAttributes',
            [&$attributes]
        );

        return $attributes;

    }

    /**
     * @inheritdoc
     */
    public static function defineSortableAttributes()
    {

        // Default attributes
        $attributes = [
            'status' => Craft::t('rating', 'Status'),
            'ownerId' => Craft::t('rating', 'Owner'),
            'collectionId' => Craft::t('rating', 'Collection')
        ];

        // Allow plugins to modify the attributes
        Craft::$app->getPlugins()->call(
            'modifyRatingSortableAttributes',
            [&$attributes]
        );

        return $attributes;

    }


    /**
     * @inheritdoc
     */
    public static function defineTableAttributes($source = null)
    {

        // Default attributes
        $attributes = [
            'rating' => Craft::t('rating', 'Rating'),
            'element' => Craft::t('rating', 'Element'),
            'publisher' => Craft::t('rating', 'Publisher'),
            'collection' => Craft::t('rating', 'Collection')
        ];

        /** @var FieldModel $ratingField */
        foreach (RatingPlugin::getInstance()->getField()->findAll() as $ratingField) {
            $attributes[$ratingField->handle] = $ratingField->name;
        }

        // Creation Date
        $attributes['dateCreated'] = Craft::t('rating', 'Date Created');

        // Allow plugins to modify the attributes
        Craft::$app->getPlugins()->call('modifyRatingTableAttributes',
            [&$attributes, $source]);

        return $attributes;

    }

    /**
     *
     * @inheritdoc
     *
     * @param Rating $element
     */
    public static function getTableAttributeHtml(ElementInterface $element, $attribute)
    {

        // First give plugins a chance to set this
        $pluginAttributeHtml = Craft::$app->getPlugins()->callFirst(
            'getRatingTableAttributeHtml',
            [$element, $attribute],
            true
        );

        if ($pluginAttributeHtml !== null) {
            return $pluginAttributeHtml;
        }

//        // Field attribute
//        if (StringHelper::startsWith($attribute, FieldService::FIELD_PREFIX)) {
//
//            // Strip prefix
//            $attribute = StringHelper::removeLeft($attribute, FieldService::FIELD_PREFIX);
//
//            // Get rating field
//            if ($ratingField = $element->getRatingField($attribute)) {
//                return $ratingField->value;
//            }
//
//            return 'n/a';
//
//            // Standard attribute
//        } else {

            switch ($attribute) {

                case 'status' :
                    $availableStatuses = self::getStatuses();
                    if (array_key_exists($element->$attribute, $availableStatuses)) {
                        return $availableStatuses[$element->$attribute];
                    }

                    break;

                case 'element' :
                    return $element->getElement();

                case 'publisher' :
                    return $element->getPublisher();

                case 'collection' :
                    $collection = $element->getCollection();

                    return '<a href="' . UrlHelper::getCpUrl('/rating/settings/collection/' . $element->collectionId) . '">' . $collection->name . '</a>';

            }

//        }

        return parent::getTableAttributeHtml($element, $attribute);

    }

    /**
     * @inheritdoc
     */
    public static function getEditorHtml(ElementInterface $element)
    {
        $html = Craft::$app->getView()->renderTemplate(
            'rating/_admin/editorHtml',
            ['rating' => $element]
        );

        $html .= parent::getEditorHtml($element);

        return $html;
    }

    /**
     * @inheritdoc Element::saveElement()
     *
     * @param Rating $element
     */
    public static function saveElement(ElementInterface $element, $params)
    {

        if (isset($params['collectionId'])) {
            $element->collectionId = $params['collectionId'];
        }

        if (isset($params['elementId'])) {
            $element->elementId = $params['elementId'];
        }

        if (isset($params['ownerId'])) {
            $element->ownerId = $params['ownerId'];
        }

        if (isset($params['name'])) {
            $element->name = $params['name'];
        }

        if (isset($params['email'])) {
            $element->email = $params['email'];
        }

        if (isset($params['status'])) {
            $element->status = $params['status'];
        }

        // todo - add additional fields

        return RatingPlugin::getInstance()->getRating()->save($element);

    }

    /************************************************************
     * PUBLISHER
     ************************************************************/

    public function getPublisher()
    {
        // Return owner is present
        if ($ownerElement = $this->getOwner()) {

            return $ownerElement;

        }

        return $this->name;

    }

    /************************************************************
     * MISC
     ************************************************************/

    /**
     * Returns the string representation of the element.
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return Craft::t('rating', 'Rating for:') . ' ' . (string)$this->getElement()->getRef();
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (parent::__isset($name) || $this->findRatingField($name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        // Give custom fields priority over other getters so we have a chance to prepare their values
        $field = $this->findRatingField($name);
        if ($field !== null) {
            return $this->getRatingFieldValue($name);
        }
        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        $names = parent::attributes();
        $class = new \ReflectionClass(behaviors\Rating::className());

        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ('owner' !== $names && !in_array($property->getName(), $names)) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }

}
