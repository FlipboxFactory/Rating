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

namespace craft\plugins\rating\services\traits;

use Craft;
use craft\app\base\Element as BaseElement;
use craft\app\elements\db\ElementQuery;
use craft\app\errors\Exception;

trait Element
{

    /**
     * @var array of all elements
     */
    private $allElements;

    /**
     * @var array of all elements indexed by numeric Id
     */
    private $elementsById = [];

    /**
     * @var array of all elements indexed by string
     */
    private $elementsByString = [];

    /*******************************************
     * ABSTRACT
     *******************************************/

    /**
     * @return string
     */
    abstract protected function getElementClassName();

    /**
     * @throws Exception
     */
    abstract protected function notFoundException();

    /**
     * @param null $id
     * @throws Exception
     */
    abstract protected function notFoundByIdException($id = null);

    /**
     * @param null $string
     * @throws Exception
     */
    abstract protected function notFoundByStringException($string = null);


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param BaseElement $element
     * @return $this
     */
    public function cacheElement(BaseElement $element)
    {

        // Check if already in cache by id
        if (!$this->isElementCachedById($element->id)) {

            // Cache it
            $this->elementsById[$element->id] = $element;

        }

        // Check if already in cache by uri
        if (!$this->isElementCachedByString($element->uri)) {

            // Cache it
            $this->elementsByString[$element->uri] = $element;

        }

        return $this;

    }

    /**
     * Find an existing cached model by ID
     *
     * @param $id
     * @return null
     */
    public function findCachedElementById($id)
    {

        // Check if already in cache
        if ($this->isElementCachedById($id)) {
            return $this->elementsById[$id];
        }

        return null;

    }

    /**
     * Find an existing cached model by string
     *
     * @param $string
     * @return null
     */
    public function findCachedElementByString($string)
    {

        // Check if already in cache
        if ($this->isElementCachedByString($string)) {
            return $this->elementsByString[$string];
        }

        return null;

    }

    /**
     * Identify whether a model is cached by ID
     *
     * @param $id
     * @return bool
     */
    public function isElementCachedById($id)
    {
        return array_key_exists($id, $this->elementsById);
    }

    /**
     * Identify whether a model is cached by handle
     *
     * @param $string
     * @return bool
     */
    public function isElementCachedByString($string)
    {
        return array_key_exists($string, $this->elementsByString);
    }


    /*******************************************
     * FIND
     *******************************************/

    /**
     * @param null $indexBy
     * @return BaseElement[]
     */
    public function findAll($indexBy = null)
    {

        // Check cache
        if (is_null($this->allElements)) {

            // Empty array
            $this->allElements = [];

            $elementClass = $this->getElementClassName();

            // Find elements
            if ($allElements = $elementClass::find()
                ->status(null)
                ->localeEnabled(false)
                ->all()
            ) {

                foreach ($allElements as $element) {

                    // Perhaps in ID cache
                    if ($exitingElement = $this->findCachedElementById($element->id)) {

                        // Clear
                        unset($element);

                        // Override element
                        $element = $exitingElement;

                    } else {

                        // Cache it
                        $this->cacheElement($element);

                    }

                    // Cache all
                    $this->allElements[] = $element;

                }

            }

        }

        if (!$indexBy) {

            $elements = $this->allElements;

        } else {

            $elements = [];

            foreach ($this->allElements as $element) {

                $elements[$element->$indexBy] = $element;

            }

        }

        return $elements;

    }

    /**
     * @return array
     */
    public function findAllElementIds()
    {

        if ($allElements = $this->findAll('id')) {

            return array_keys($allElements);

        }

        return [];
    }

    /**
     * Find an element by either id or string.
     *
     * @param $identifier
     * @return BaseElement|null
     */
    public function find($identifier)
    {

        // already an element
        if ($identifier instanceof BaseElement) {

            return $identifier;

        } elseif (is_numeric($identifier)) {

            return $this->findById($identifier);

        }

        return $this->findByString($identifier);

    }

    /**
     * Find by id.
     *
     * @param $id
     * @return BaseElement|null
     */
    public function findById($id)
    {
        return Craft::$app->getElements()->getElementById(
            $id,
            $this->getElementClassName()
        );
    }

    /**
     * Find by string.
     *
     * @param $string
     * @return BaseElement|null
     */
    public function findByString($string)
    {

        return Craft::$app->getElements()->getElementByUri(
            $string
        );

    }

    /**
     * Find by criteria
     *
     * @param $criteria
     * @return mixed
     */
    public function findByCriteria($criteria)
    {

        return $this->getCriteria($criteria)->one();

    }

    /**
     * Find an array by criteria
     *
     * @param $criteria
     * @return mixed
     */
    public function findAllByCriteria($criteria)
    {

        return $this->getCriteria($criteria)->all();

    }


    /*******************************************
     * GET
     *******************************************/

    /**
     * Get by either id or string.
     *
     * @param $identifier
     * @return BaseElement|null
     * @throws Exception
     */
    public function get($identifier)
    {

        if (!$orgElement = $this->find($identifier)) {

            $this->notFoundException();

        }

        return $orgElement;

    }

    /**
     * Get by id.
     *
     * @param $id
     * @return BaseElement|null
     * @throws Exception
     */
    public function getById($id)
    {

        if (!$orgElement = $this->findById($id)) {

            $this->notFoundByIdException($id);

        }

        return $orgElement;

    }

    /**
     * Get by string.
     *
     * @param $string
     * @return BaseElement|null
     * @throws Exception
     */
    public function getByString($string)
    {

        if (!$orgElement = $this->findByString($string)) {

            $this->notFoundByStringException($string);

        }

        return $orgElement;

    }

    /**
     * Get criteria
     *
     * @param $criteria
     * @return ElementQuery
     */
    public function getCriteria($criteria)
    {

        $elementClass = $this->getElementClassName();

        return $elementClass::find($criteria);

    }

    /**
     * Get by criteria
     *
     * @param $criteria
     * @return mixed
     */
    public function getByCriteria($criteria)
    {

        if (!$orgElement = $this->findByCriteria($criteria)) {

            $this->notFoundException();

        }

        return $orgElement;

    }

    /**
     * Get an array by criteria
     *
     * @param $criteria
     * @return mixed
     */
    public function getAllByCriteria($criteria)
    {

        $orgElements = $this->findAllByCriteria($criteria);

        if (empty($orgElements)) {

            $this->notFoundException();

        }

        return $orgElements;

    }

}
