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
use yii\db\ActiveQueryInterface;

/**
 * Class Collection record.
 *
 * @property integer $id                        ID
 * @property integer $collectionId              Collection ID
 * @property integer $fieldId                   Field ID
 * @property boolean $required                  Required
 * @property string $sortOrder                  Sort Order
 * @property ActiveQueryInterface $collection   Collection
 * @property ActiveQueryInterface $field        Field
 *
 * @author Flipbox Factory. <support@flipboxfactory.com>
 * @since  3.0
 */
class CollectionField extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ratingcollectionfields}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['collectionId'],
                'unique',
                'targetAttribute' => ['collectionId', 'fieldId']
            ],
        ];
    }

    /**
     * Returns the field layout field’s layout.
     *
     * @return \yii\db\ActiveQueryInterface The relational query object.
     */
    public function getCollection()
    {
        return $this->hasOne(Collection::className(), ['id' => 'collectionId']);
    }

    /**
     * Returns the field layout field’s field.
     *
     * @return \yii\db\ActiveQueryInterface The relational query object.
     */
    public function getField()
    {
        return $this->hasOne(Field::className(), ['id' => 'fieldId']);
    }

}