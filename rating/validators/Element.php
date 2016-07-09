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

namespace craft\plugins\rating\validators;

use Craft;
use craft\app\base\ElementInterface;
use craft\plugins\rating\models\Collection;
use craft\plugins\rating\Plugin as RatingPlugin;
use craft\plugins\rating\records\Rating;
use yii\validators\Validator;

class Element extends Validator
{

    /**
     * @var boolean whether this validation rule should be skipped if the attribute value
     * is null or an empty string.
     */
    public $skipOnEmpty = false;

    /**
     * @param Rating $object
     * @param $attribute
     *
     * @return void
     */
    public function validateAttribute($object, $attribute)
    {

        /** @var Collection $collectionModel */
        if ($collectionModel = RatingPlugin::getInstance()->getCollection()->get($object->collectionId)) {

            /** @var ElementInterface $element */
            if ($element = Craft::$app->getElements()->getElementById($object->{$attribute})) {

                if ($element->className() != $collectionModel->elementType) {

                    $this->addError($object, $attribute,
                        Craft::t('rating', '{attribute} must be a "{elementType}" element type',
                            ['attribute' => $object->{$attribute}, 'elementType' => $collectionModel->elementType]
                        )
                    );

                }

            } else {

                $this->addError($object, 'collectionId',
                    Craft::t('rating', '"{collectionId}" is an invalid collection id',
                        ['collectionId' => $object->collectionId]
                    )
                );

            }

        } else {

            $this->addError($object, $attribute,
                Craft::t('rating', '{attribute} is an invalid element id',
                    ['attribute' => $object->{$attribute}]
                )
            );

        }

    }

}
