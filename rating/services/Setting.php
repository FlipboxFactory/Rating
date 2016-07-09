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
use craft\app\helpers\StringHelper;
use craft\app\models\FieldLayout as FieldLayoutModel;
use craft\plugins\rating\models\Settings;
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\records\Rating as RatingRecord;
use yii\base\Component;

class Setting extends Component
{

    /**
     * Save plugin settings
     *
     * @param Settings $settingsModel
     * @return bool|int
     */
    public function save(Settings $settingsModel)
    {

        // Validate
        if ($settingsModel->validate()) {

            // Save
            if ($this->_save($settingsModel->getAttributes())) {

                // Alter status enums
                return $this->_alterStatusColumn();

            }

        }

        return false;

    }

    /**
     * Update field version
     *
     * @return bool
     */
    public function updateFieldVersion()
    {

        // Get existing settings
        $settingsModel = RatingPlugin::getInstance()->getSettings();

        // Set layout id
        $settingsModel->fieldVersion = StringHelper::randomString(12);

        return $this->_save($settingsModel->getAttributes());

    }

    /**
     * Save default layout
     *
     * @param FieldLayoutModel $fieldLayout
     * @return bool
     */
    public function saveLayout(FieldLayoutModel $fieldLayout = null)
    {

        // Delete existing layouts
        Craft::$app->getFields()->deleteLayoutsByType(RatingPlugin::className());

        // Save settings indicating no content
        if (is_null($fieldLayout)) {

            return $this->_save([
                'fieldLayoutId' => null
            ]);

        }

        // Set layout type
        $fieldLayout->type = RatingPlugin::className();

        // Save default field layout
        if (Craft::$app->getFields()->saveLayout($fieldLayout)) {

            // Get existing settings
            $settingsModel = RatingPlugin::getInstance()->getSettings();

            // Set layout id
            $settingsModel->fieldLayoutId = $fieldLayout->id;

            return $this->_save($settingsModel->getAttributes());

        }

        return false;

    }

    /**
     * Save a settings array
     *
     * @param array $settings
     * @return bool
     */
    private function _save(array $settings)
    {
        return Craft::$app->getPlugins()->savePluginSettings(RatingPlugin::getInstance(), $settings);
    }

    /**
     * @return bool|int
     * @throws \yii\db\Exception
     */
    private function _alterStatusColumn()
    {

        // Make sure we're working with the latest
        Craft::$app->getDb()->schema->refresh();

        // Verify column exists
        if (Craft::$app->getDb()->columnExists(RatingRecord::tableName(), 'status')) {

            $statuses = RatingPlugin::getInstance()->getSettings()->getStatuses();

            // Create enum string
            $statusesString = "'" . StringHelper::toString(array_keys($statuses), "','") . "'";

            // Get first status
            reset($statuses);

            // Default publisher type
            $defaultStatus = key($statuses);

            // TODO - change values of statuses that do not match current

            // Assembled full column type
            $columnType = 'enum(' . $statusesString . ') COLLATE utf8_unicode_ci NOT NULL DEFAULT \'' . $defaultStatus . '\'';

            // Execute column changes
            Craft::$app->getDb()->createCommand()->alterColumn(
                RatingRecord::tableName(),
                'status',
                $columnType
            )->execute();

            return true;

        }

        return false;

    }

}
