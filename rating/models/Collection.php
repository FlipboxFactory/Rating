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

use Craft;
use craft\app\base\Model;
use craft\app\errors\Exception;
use craft\app\models\FieldLayout;
use craft\plugins\rating\Plugin as RatingPlugin;

class Collection extends Model
{

    /**
     * @var integer ID
     */
    public $id;

    /**
     * @var string Handle
     */
    public $handle;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Element Type
     */
    public $elementType;

    /**
     * @var integer Field layout ID
     */
    public $fieldLayoutId;

    /**
     * @var \DateTime Date updated
     */
    public $dateUpdated;

    /**
     * @var \DateTime Date created
     */
    public $dateCreated;

    /**
     * @var array Field Models
     */
    private $ratingFieldModels;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'craft\app\behaviors\FieldLayoutBehavior',
                'elementType' => self::className()
            ],
        ];
    }

    /**
     * Auto-magically return the collection name
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Associate field layout to model
     *
     * @param FieldLayout $fieldLayout
     */
    public function setFieldLayout(FieldLayout $fieldLayout)
    {
        $fieldLayout->type = self::className();
        parent::setFieldlayout($fieldLayout);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getElementType()
    {
        if (!$this->elementType) {
            throw new Exception(Craft::t('rating', 'Element type is not defined.'));
        }

        return $this->elementType;
    }

    /**
     * @return array
     */
    public function getRatingFields($indexBy = null)
    {
        // Check cache
        if (is_null($this->ratingFieldModels)) {

            $this->ratingFieldModels = RatingPlugin::getInstance()->getCollection()->getRatingFields($this);

        }

        if (!$indexBy) {

            $models = $this->ratingFieldModels;

        } else {

            $models = [];

            foreach ($this->ratingFieldModels as $model) {

                $models[$model->$indexBy] = $model;

            }

        }

        return $models;

    }

    /**
     * Associate an collection to the element
     *
     * @param $ratingFields
     * @return $this
     */
    public function setRatingFields(array $ratingFields)
    {

        // Clear cache
        $this->ratingFieldModels = null;

        foreach ($ratingFields as $ratingField) {

            if (!$ratingField instanceof Field) {
                $ratingField = Field::create($ratingField);
            }

            $this->ratingFieldModels[] = $ratingField;

        }

        return $this;

    }

}
