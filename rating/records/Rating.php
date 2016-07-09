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

namespace craft\plugins\rating\records;

use Craft;
use craft\app\db\ActiveRecord;
use craft\app\records\Element;
use craft\plugins\rating\Plugin as RatingPlugin;
use yii\db\ActiveQueryInterface;

/**
 * Class Rating record.
 *
 * @property integer $id                        ID
 * @property integer $elementId                 Element ID
 * @property integer $ownerId                  Owner ID
 * @property integer $collectionId              Collection ID
 * @property string $name                       Publisher Name
 * @property string $email                      Publisher Email
 * @property string $status                     Status
 * @property ActiveQueryInterface $element      Element
 * @property ActiveQueryInterface $owner       User
 * @property ActiveQueryInterface $collection   Collection
 *
 * @author Flipbox Factory. <support@flipboxfactory.com>
 * @since  3.0
 */
class Rating extends ActiveRecord
{

    /**
     * Set some default attribute values
     */
    public function init()
    {

        // set default owner
        if ($currentUser = Craft::$app->getUser()->getIdentity()) {
            $this->ownerId = $currentUser->id;
        }

        // Default status
        $this->status = RatingPlugin::getInstance()->getSettings()->defaultStatus();

        parent::init();

    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ratings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['status'],
                'in',
                'range' => array_keys(RatingPlugin::getInstance()->getSettings()->getStatuses())
            ],
            [
                ['elementId'],
                'craft\\plugins\\rating\\validators\\Element'
            ],
            [
                ['elementId', 'collectionId', 'status'],
                'required'
            ]
        ];

    }


    /**
     * When updating, ignore the 'status' attribute.
     *
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool|int
     * @throws \Exception
     */
    public function update($runValidation = true, $attributeNames = null)
    {

        if (is_null($attributeNames)) {

            // Get all attributes
            $attributeNames = $this->attributes();

            // Prevent updating status
            if (array_key_exists('status', $attributeNames)) {
                unset($attributeNames['status']);
            }

        }

        return parent::update($runValidation, $attributeNames);

    }

    /**
     * When validating, ignore the 'status' attribute.
     *
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {

        // If new or defined, validate normally
        if ($this->getIsNewRecord() || !is_null($attributeNames)) {

            return parent::validate($attributeNames, $clearErrors);

        }

        // Get active attributes
        $activeAttributes = $this->activeAttributes();

        // look for 'status'
        if (false !== ($key = array_search('status', $activeAttributes))) {

            // Unset 'status' attribute
            unset($activeAttributes[$key]);

        }

        // Validate everything except the status
        return parent::validate($activeAttributes, $clearErrors);

    }

    /**
     * Returns the element.
     *
     * @return \yii\db\ActiveQueryInterface The relational query object.
     */
    public function getRating()
    {
        return $this->hasOne(Element::className(), ['id' => 'id']);
    }

    /**
     * Returns the associated element.
     *
     * @return \yii\db\ActiveQueryInterface The relational query object.
     */
    public function getElement()
    {
        return $this->hasOne(Element::className(), ['id' => 'elementId']);
    }

    /**
     * Returns the owner element.
     *
     * @return \yii\db\ActiveQueryInterface The relational query object.
     */
    public function getOwner()
    {
        return $this->hasOne(Element::className(), ['id' => 'ownerId']);
    }

    /**
     * Returns the collection.
     *
     * @return \yii\db\ActiveQueryInterface
     */
    public function getCollection()
    {
        return $this->hasOne(Collection::className(), ['id' => 'collectionId']);
    }

}
