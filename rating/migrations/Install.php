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

namespace craft\plugins\rating\migrations;

use craft\app\db\InstallMigration;
use craft\app\helpers\StringHelper;
use craft\app\records\Element as ElementRecord;
use craft\app\records\FieldLayout as FieldLayoutRecord;
use craft\app\records\User as UserRecord;
use craft\plugins\rating\models\Settings as RatingSettings;
use craft\plugins\rating\records\Collection as CollectionRecord;
use craft\plugins\rating\records\CollectionField as CollectionFieldRecord;
use craft\plugins\rating\records\Field as FieldRecord;
use craft\plugins\rating\records\Rating as RatingRecord;

class Install extends InstallMigration
{

    /**
     * @inheritdoc
     */
    protected function defineSchema()
    {

        // Default statuses
        $defaultStatuses = RatingSettings::defaultStatuses();

        // Default status enum string
        $defaultStatusesString = "'" . StringHelper::toString(array_keys($defaultStatuses), "','") . "'";

        // Get first status
        reset($defaultStatuses);

        // Default status
        $defaultStatus = key($defaultStatuses);

        return [
            CollectionFieldRecord::tableName() => [
                'columns' => [
                    'collectionId' => 'integer(11) NOT NULL',
                    'fieldId' => 'integer(11) NOT NULL',
                    'required' => 'smallint(1) unsigned NOT NULL DEFAULT \'0\'',
                    'sortOrder' => 'smallint(4) DEFAULT NULL'
                ],
                'indexes' => [
                    ['collectionId,fieldId', true],
                    ['fieldId', false],
                    ['sortOrder', false]
                ],
                'foreignKeys' => [
                    [
                        'collectionId',
                        CollectionRecord::tableName(),
                        'id',
                        'CASCADE',
                        null
                    ],
                    [
                        'fieldId',
                        FieldRecord::tableName(),
                        'id',
                        'CASCADE',
                        null
                    ]
                ]
            ],
            RatingRecord::tableName() => [
                'columns' => [
                    'collectionId' => 'integer(11) NOT NULL',
                    'elementId' => 'integer(11) NOT NULL',
                    'ownerId' => 'integer(11) DEFAULT NULL',
                    'name' => 'string(255) COLLATE utf8_unicode_ci DEFAULT NULL',
                    'email' => 'string(255) COLLATE utf8_unicode_ci DEFAULT NULL',
                    'status' => 'enum(' . $defaultStatusesString . ') COLLATE utf8_unicode_ci NOT NULL DEFAULT \'' . $defaultStatus . '\''
                ],
                'indexes' => [
                    ['collectionId', false],
                    ['elementId', false],
                    ['ownerId', false]
                ],
                'foreignKeys' => [
                    [
                        'collectionId',
                        CollectionRecord::tableName(),
                        'id',
                        'CASCADE',
                        null
                    ],
                    [
                        'elementId',
                        ElementRecord::tableName(),
                        'id',
                        'CASCADE',
                        null
                    ],
                    [
                        'ownerId',
                        UserRecord::tableName(),
                        'id',
                        'CASCADE',
                        null
                    ]
                ]
            ],
            CollectionRecord::tableName() => [
                'columns' => [
                    'name' => 'string(255) COLLATE utf8_unicode_ci NOT NULL',
                    'handle' => 'string(255) COLLATE utf8_unicode_ci NOT NULL',
                    'elementType' => 'string(150) COLLATE utf8_unicode_ci NOT NULL',
                    'fieldLayoutId' => 'integer(11) DEFAULT NULL'
                ],
                'indexes' => [
                    ['name', false],
                    ['handle', true],
                    ['elementType', false],
                    ['fieldLayoutId', false]
                ],
                'foreignKeys' => [
                    [
                        'fieldLayoutId',
                        FieldLayoutRecord::tableName(),
                        'id',
                        'SET NULL',
                        null
                    ]
                ]
            ],
            FieldRecord::tableName() => [
                'columns' => [
                    'name' => 'string(255) COLLATE utf8_unicode_ci NOT NULL',
                    'handle' => 'string(255) COLLATE utf8_unicode_ci NOT NULL',
                    'min' => 'smallint(4) DEFAULT 1',
                    'max' => 'smallint(4) DEFAULT 5',
                    'increment' => 'smallint(4) DEFAULT 1',
                    'precision' => 'smallint(4) DEFAULT 0'
                ],
                'indexes' => [
                    ['name', true],
                    ['handle', true],
                    ['min', false],
                    ['max', false]
                ]
            ]
        ];

    }
}
