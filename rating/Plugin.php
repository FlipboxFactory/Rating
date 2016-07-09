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

namespace craft\plugins\rating;

use Craft;
use craft\app\base\Plugin as BasePlugin;
use craft\app\db\Query;
use craft\app\helpers\IOHelper;
use craft\app\helpers\UrlHelper;
use craft\plugins\rating\models\Settings as RatingSettings;
use craft\plugins\rating\records\Field as RatingFieldRecord;
use craft\plugins\rating\web\twig\variables\Rating as RatingVariable;
use Yii;

class Plugin extends BasePlugin
{

    /**
     * Custom controller mapping to eliminate 'rating/rating' actions
     *
     * @var array
     */
    public $controllerMap = [
        'save' => [
            'class' => 'craft\\plugins\\rating\\controllers\\RatingController',
            'defaultAction' => 'save'
        ],
        'delete' => [
            'class' => 'craft\\plugins\\rating\\controllers\\RatingController',
            'defaultAction' => 'delete'
        ]
    ];

    /**
     * @inheritdoc
     */
    public static function hasCpSection()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function registerCpRoutes()
    {
        return [
            'rating/settings' => 'rating/settings/general/view-index',
            'rating/settings/collection' => 'rating/settings/collection/view-index',
            'rating/settings/collection/new' => 'rating/settings/collection/view-upsert',
            'rating/settings/collection/<collectionIdentifier:\d+>' => 'rating/settings/collection/view-upsert',
            'rating/settings/field' => 'rating/settings/field/view-index',
            'rating/settings/field/new' => 'rating/settings/field/view-upsert',
            'rating/settings/field/<fieldIdentifier:\d+>' => 'rating/settings/field/view-upsert',
            'rating' => 'rating/rating/view-index',
            'rating/<collectionIdentifier:{handle}>/new' => 'rating/rating/view-upsert',
            'rating/<collectionIdentifier:{handle}>/<ratingIdentifier:\d+>/' => 'rating/rating/view-upsert'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getVariableDefinition()
    {
        return new RatingVariable();
    }

    /**
     * @inheritdoc
     *
     * @return Plugin
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * @inheritdoc
     *
     * @return RatingSettings
     */
    public function getSettings()
    {
        return parent::getSettings();
    }

    /**
     * @return RatingSettings
     */
    protected function createSettingsModel()
    {
        return new RatingSettings();
    }

    /**
     * Redirect to our plugin specific settings
     *
     * @throws \yii\base\ExitException
     */
    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(UrlHelper::getCpUrl('rating/settings'));
        Craft::$app->end();
    }

    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @return \craft\plugins\rating\services\Rating
     */
    public function getRating()
    {
        return $this->get('rating');
    }

    /**
     * @return \craft\plugins\rating\services\Field
     */
    public function getField()
    {
        return $this->get('field');
    }

    /**
     * @return \craft\plugins\rating\services\Setting
     */
    public function getSetting()
    {
        return $this->get('setting');
    }

    /**
     * @return \craft\plugins\rating\services\Collection
     */
    public function getCollection()
    {
        return $this->get('collection');
    }

    /**
     * @return \craft\plugins\rating\services\CollectionField
     */
    public function getCollectionField()
    {
        return $this->get('collectionField');
    }


    /**
     * @inheritdoc
     */
    public function init()
    {

        // Parent
        parent::init();

        // Register our autoloader to build trait/behavior
        spl_autoload_register(['craft\\plugins\\rating\\Plugin', 'autoload'], true, true);

    }


    /**
     * Plugin autoload
     *
     * @param $className
     */
    public static function autoload($className)
    {

        if (
            $className === 'craft\\plugins\\rating\\elements\\behaviors\\Rating' ||
            $className === 'craft\\plugins\\rating\\elements\\traits\\Rating' ||
            $className === 'craft\\plugins\\rating\\elements\\db\\behaviors\\Rating' ||
            $className === 'craft\\plugins\\rating\\elements\\db\\traits\\Rating'
        ) {

            $storedFieldVersion = self::getInstance()->getSettings()->fieldVersion;
            $compiledClassesPath = Craft::$app->getPath()->getRuntimePath().'/compiled_classes/rating';

            $ratingBehaviorFile = $compiledClassesPath.'/elements/behaviors/Rating.php';
            $ratingTraitFile = $compiledClassesPath.'/elements/traits/Rating.php';
            $ratingQueryBehaviorFile = $compiledClassesPath.'/elements/db/behaviors/Rating.php';
            $ratingQueryTraitFile = $compiledClassesPath.'/elements/db/traits/Rating.php';

            if (
                static::_isRatingFieldAttributesFileValid($ratingBehaviorFile,
                    $storedFieldVersion) &&
                static::_isRatingFieldAttributesFileValid($ratingTraitFile,
                    $storedFieldVersion) &&
                static::_isRatingFieldAttributesFileValid($ratingQueryBehaviorFile,
                    $storedFieldVersion) &&
                static::_isRatingFieldAttributesFileValid($ratingQueryTraitFile,
                    $storedFieldVersion)
            ) {
                return;
            }

            // Get the field handles
            $fieldHandles = (new Query())
                ->select('handle')
                ->distinct(true)
                ->from(RatingFieldRecord::tableName())
                ->column();

            $properties = [];
            $methods = [];
            $propertyDocs = [];
            $methodDocs = [];

            foreach ($fieldHandles as $handle) {
                $properties[] = <<<EOD
	/**
	 * @var mixed Value for field with the handle “{$handle}”.
	 */
	public \${$handle};
EOD;

                $methods[] = <<<EOD
	/**
	 * Sets the [[{$handle}]] property.
	 * @param mixed \$value The property value
	 * @return \\yii\\base\\Component The behavior’s owner component
	 */
	public function {$handle}(\$value)
	{
		\$this->{$handle} = \$value;
		return \$this->owner;
	}
EOD;

                $propertyDocs[] = " * @property mixed \${$handle} Value for the field with the handle “{$handle}”.";
                $methodDocs[] = " * @method \$this {$handle}(\$value) Sets the [[{$handle}]] property.";
            }

            static::_writeRatingFieldAttributesFile(
                Craft::$app->getPath()->getPluginsPath().'/rating/elements/behaviors/Rating.php.template',
                ['{VERSION}', '/* PROPERTIES */'],
                [$storedFieldVersion, implode("\n\n", $properties)],
                $ratingBehaviorFile
            );

            static::_writeRatingFieldAttributesFile(
                Craft::$app->getPath()->getPluginsPath().'/rating/elements/traits/Rating.php.template',
                ['{VERSION}', '{PROPERTIES}'],
                [$storedFieldVersion, implode("\n", $propertyDocs)],
                $ratingTraitFile
            );

            static::_writeRatingFieldAttributesFile(
                Craft::$app->getPath()->getPluginsPath().'/rating/elements/db/behaviors/Rating.php.template',
                ['{VERSION}', '/* METHODS */'],
                [$storedFieldVersion, implode("\n\n", $methods)],
                $ratingQueryBehaviorFile
            );

            static::_writeRatingFieldAttributesFile(
                Craft::$app->getPath()->getPluginsPath().'/rating/elements/db/traits/Rating.php.template',
                ['{VERSION}', '{METHODS}'],
                [$storedFieldVersion, implode("\n", $methodDocs)],
                $ratingQueryTraitFile
            );
        }
    }

    /**
     * Determines if a field attribute file is valid.
     *
     * @param $path
     * @param $storedFieldVersion
     *
     * @return boolean
     */
    private static function _isRatingFieldAttributesFileValid($path, $storedFieldVersion)
    {
        if (file_exists($path)) {
            // Make sure it's up-to-date
            $f = fopen($path, 'r');
            $line = fgets($f);
            fclose($f);

            if (preg_match('/\/\/ v([a-zA-Z0-9]{12})/', $line, $matches)) {
                if ($matches[1] == $storedFieldVersion) {
                    include($path);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Writes a field attributes file.
     *
     * @param $templatePath
     * @param $search
     * @param $replace
     * @param $destinationPath
     */
    private static function _writeRatingFieldAttributesFile($templatePath, $search, $replace, $destinationPath)
    {
        $fileContents = IOHelper::getFileContents($templatePath);
        $fileContents = str_replace($search, $replace, $fileContents);
        IOHelper::writeToFile($destinationPath, $fileContents);
        include($destinationPath);
    }

}
