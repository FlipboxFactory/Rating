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

namespace craft\plugins\rating\models;

use craft\app\base\Model;
use craft\app\helpers\StringHelper;

class Settings extends Model
{

    /**
     * @var mixed Statuses
     */
    public $statuses;

    /**
     * @var string Rating field version
     */
    public $fieldVersion;

    /**
     * The default statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
    const STATUS_DISABLE = 'disabled';

    /**
     * Get an array of statuses
     *
     * @return array
     */
    public function getStatuses()
    {

        if (is_null($this->statuses)) {
            return self::defaultStatuses();
        }

        return $this->statuses;

    }

    /**
     * Get the default status
     *
     * @return mixed
     */
    public function defaultStatus()
    {
        $allStatuses = $this->getStatuses();
        reset($allStatuses);

        return key($allStatuses);
    }

    /**
     * Default array of statuses
     *
     * @return array
     */
    public static function defaultStatuses()
    {
        return [
            static::STATUS_ACTIVE => StringHelper::uppercaseFirst(static::STATUS_ACTIVE),
            static::STATUS_PENDING => StringHelper::uppercaseFirst(static::STATUS_PENDING),
            static::STATUS_DISABLE => StringHelper::uppercaseFirst(static::STATUS_DISABLE)
        ];
    }

}
