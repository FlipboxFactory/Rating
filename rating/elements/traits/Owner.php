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
use craft\app\elements\User as UserElement;
use craft\app\errors\Exception;

trait Owner
{
    /**
     * @var integer Owner ID
     */
    public $ownerId;

    /**
     * @var UserElement The cached owner element associated to this element. Set by [[getOwner()]].
     */
    private $ownerElement;

    /**
     * Identify whether element has an owner
     *
     * @return bool
     */
    public function hasOwner()
    {
        return $this->ownerElement instanceof UserElement;
    }

    /**
     * Get the owner of the element
     *
     * @param bool $strict
     * @return UserElement
     * @throws Exception
     */
    public function getOwner($strict = true)
    {

        // Check cache
        if (is_null($this->ownerElement)) {

            // Check property
            if (!empty($this->ownerId)) {

                // Find element
                if ($ownerElement = Craft::$app->getUsers()->getUserById($this->ownerId)) {

                    // Set
                    $this->setOwner($ownerElement);

                } else {

                    // Clear property
                    $this->ownerId = null;

                    // Prevent subsequent look-ups
                    $this->ownerElement = false;

                    // Throw and exception?
                    if($strict) {

                        // Element not found
                        throw new Exception(Craft::t(
                            'app',
                            'Owner does not exist.'
                        ));

                    }

                }

            }

        } else {

            // Cache changed?
            if (($this->ownerId && $this->ownerElement === false) || ($this->ownerId !== $this->ownerElement->getId())) {

                // Clear cache
                $this->ownerElement = null;

                // Again
                return $this->getOwner();

            }

        }

        return $this->hasOwner() ? $this->ownerElement : null;

    }

    /**
     * Associate an owner to the element
     *
     * @param $owner
     * @return $this
     */
    public function setOwner($owner)
    {

        // Clear cache
        $this->ownerElement = null;

        // Find element
        if (!$owner = $this->findUserElement($owner)) {

            // Clear property / cache
            $this->ownerId = $this->ownerElement = null;

        } else {

            // Set property
            $this->ownerId = $owner->getId();

            // Set cache
            $this->ownerElement = $owner;

        }

        return $this;

    }

    /**
     * @param string|UserElement $user
     * @return bool
     */
    public function isOwner($user = 'CURRENT_USER')
    {

        if ('CURRENT_USER' === $user) {

            // Current user
            $element = Craft::$app->getUser()->getIdentity();

        } else {

            // Find element
            $element = $this->findUserElement($user);

        }

        return ($element && $element->getId() == $this->ownerId);

    }

    /**
     * @param $user
     * @return UserElement|null
     */
    private function findUserElement($user)
    {

        // Element
        if ($user instanceof UserElement) {

            return $user;

            // Id
        } elseif (is_numeric($user)) {

            return Craft::$app->getUsers()->getUserById($user);

            // Username / Email
        } elseif (!is_null($user)) {

            return Craft::$app->getUsers()->getUserByUsernameOrEmail($user);

        }

        return null;

    }

}
