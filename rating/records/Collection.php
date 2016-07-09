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
use craft\app\records\FieldLayout;
use yii\db\ActiveQueryInterface;

/**
 * Class Collection record.
 *
 * @property integer $id                            ID
 * @property string $name                           Name
 * @property string $handle                         Handle
 * @property string $elementType                    Element Type
 * @property integer $fieldLayoutId                 Layout ID
 * @property ActiveQueryInterface $fieldLayout      Field Layout
 * @property ActiveQueryInterface $ratings          Ratings
 *
 * @author Flipbox Factory. <support@flipboxfactory.com>
 * @since  3.0
 */
class Collection extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ratingcollections}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['handle'],
                'craft\\app\\validators\\Handle',
                'reservedWords' => [
                    'archived',
                    'children',
                    'dateCreated',
                    'dateUpdated',
                    'enabled',
                    'id',
                    'link',
                    'locale',
                    'parents',
                    'siblings',
                    'uid',
                    'uri',
                    'url',
                    'ref',
                    'status',
                    'title'
                ]
            ],
            [
                ['handle'],
                'unique'
            ],
            [
                ['name', 'handle', 'elementType', 'fieldLayoutId'],
                'required'
            ],
            [
                ['name', 'handle'],
                'string',
                'max' => 255
            ],
            [
                ['elementType'],
                'string',
                'max' => 150
            ]
        ];
    }

    /**
     * Returns the field layout.
     *
     * @return \yii\db\ActiveQueryInterface The relational query object.
     */
    public function getFieldLayout()
    {
        return $this->hasOne(FieldLayout::className(), ['id' => 'fieldLayoutId']);
    }

    /**
     * Returns the elements associated to this collection.
     *
     * @return \yii\db\ActiveQueryInterface
     */
    public function getRatings()
    {
        return $this->hasMany(Rating::className(), ['id' => 'collectionId'])
            ->viaTable(Rating::tableName(), ['collectionId' => 'id']);
    }

}
