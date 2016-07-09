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
use craft\plugins\rating\services\Field as FieldService;

/**
 * Class Rating record.
 *
 * @property integer $id                            ID
 * @property integer $handle                        Handle
 * @property integer $name                          Name
 * @property integer $min                           Min
 * @property string $max                            Max
 * @property string $increment                      Increment
 * @property string $precision                      Precision
 *
 * @author Flipbox Factory. <support@flipboxfactory.com>
 * @since  3.0
 */
class Field extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ratingfields}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        // TODO: MySQL specific
        $maxHandleLength = 64 - strlen(FieldService::FIELD_PREFIX);

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
                'unique',
                'targetAttribute' => ['handle']
            ],
            [
                ['name', 'handle'],
                'required'
            ],
            [
                ['name'],
                'string',
                'max' => 255
            ],
            [
                ['handle'],
                'string',
                'max' => $maxHandleLength
            ],
            [
                ['min', 'max', 'increment'],
                'number',
                'min' => 0,
                'max' => 255,
                'integerOnly' => true
            ],
            [
                ['precision'],
                'number',
                'min' => 0,
                'max' => 10,
                'integerOnly' => true
            ]
        ];

    }

}
