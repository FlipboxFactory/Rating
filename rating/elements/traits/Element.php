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

namespace craft\plugins\rating\elements\traits;

use Craft;
use craft\app\base\ElementInterface;
use craft\app\errors\Exception;

trait Element
{
    /**
     * @var integer Element ID
     */
    public $elementId;

    /**
     * @var ElementInterface The cached element element associated to this element. Set by [[getElement()]].
     */
    private $element;

    /**
     * Identify whether element has an element
     *
     * @return bool
     */
    public function hasElement()
    {
        return $this->element instanceof ElementInterface;
    }

    /**
     * Get the element of the element
     *
     * @param bool $strict
     * @return ElementInterface
     * @throws Exception
     */
    public function getElement($strict = true)
    {

        // Check cache
        if (is_null($this->element)) {

            // Check property
            if (!empty($this->elementId)) {

                // Find element
                if ($elementElement = Craft::$app->getElements()->getElementById($this->elementId)) {

                    // Set
                    $this->setElement($elementElement);

                } else {

                    // Clear property
                    $this->elementId = null;

                    // Prevent subsequent look-ups
                    $this->element = false;

                    // Throw and exception?
                    if($strict) {

                        // Element not found
                        throw new Exception(Craft::t(
                            'app',
                            'Element does not exist.'
                        ));

                    }

                }

            }

        } else {

            // Cache changed?
            if (($this->elementId && $this->element === false) || ($this->elementId !== $this->element->getId())) {

                // Clear cache
                $this->element = null;

                // Again
                return $this->getElement();

            }

        }

        return $this->hasElement() ? $this->element : null;

    }

    /**
     * Associate an element to the element
     *
     * @param $element
     * @return $this
     */
    public function setElement($element)
    {

        // Clear cache
        $this->element = null;

        // Find element
        if (!$element = $this->findElement($element)) {

            // Clear property / cache
            $this->elementId = $this->element = null;

        } else {

            // Set property
            $this->elementId = $element->getId();

            // Set cache
            $this->element = $element;

        }

        return $this;

    }

    /**
     * Identify whether an element is the element
     *
     * @param string|ElementInterface $element
     * @return bool
     */
    public function isElement($element)
    {

        // Find element
        $element = $this->findElement($element);

        return ($element && $element->getId() == $this->elementId);

    }

    /**
     * Find an element based on an instance, id, or string
     *
     * @param $element
     * @return ElementInterface|null
     */
    private function findElement($element)
    {

        // Element
        if ($element instanceof ElementInterface) {

            return $element;

            // Id
        } elseif (is_numeric($element)) {

            return Craft::$app->getElements()->getElementById($element);

            // username / email
        } elseif (!is_null($element)) {

            return Craft::$app->getElements()->getElementByUri($element);

        }

        return null;

    }

}
