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

namespace craft\plugins\rating\web\assets;

use Craft;
use yii\web\AssetBundle;

/**
 * Application asset bundle.
 */
class Cp extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@plugins/rating/web/resources';

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/cp.css',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $useCompressedJs = (bool)Craft::$app->getConfig()->get('useCompressedJs');

        // todo - compress js and uncomment this
//        $this->js = [
//            'js/'.($useCompressedJs ? 'compressed/' : '').'cp.js',
//        ];

        $this->js = [
            'js/cp.js',
        ];

    }

}
