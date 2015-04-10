<?php namespace Permit\User;

use Permit\Permission\Holder\HolderInterface as PermissionHolder;
use Permit\Permission\Holder\GenericHolderTrait as PermissionHolderTrait;
use Permit\Groups\GenericHolderTrait as GroupHolderTrait;
use Permit\Groups\HolderInterface as GroupHolder;
use Permit\Permission\Holder\NestedHolderInterface;

class GenericUser implements UserInterface, PermissionHolder, NestedHolderInterface, GroupHolder
{

    use PermissionHolderTrait;

    use GroupHolderTrait;

    protected $authId;

    protected $guest;

    protected $system;

    /**
     * @brief returns a unique id for this user
     **/
    public function getAuthId()
    {
        return $this->authId;
    }

    public function setAuthId($authId)
    {
        $this->authId = $authId;
    }

    /**
     * @brief Returns if the user is nobody. Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isGuest()
    {
        return $this->guest;
    }

    public function setIsGuest($isGuest)
    {
        $this->guest = $isGuest;
    }

    /**
     * @brief Returns if the user is the system (like cron). Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isSystem()
    {
        return $this->system;
    }

    public function setIsSystem($isSystem)
    {
        $this->system = $isSystem;
    }

    /**
     * @brief Returns if the user is the superuser
     *
     * @return bool
     **/
    public function isSuperUser()
    {
        return $this->isSystem();
    }

    /**
     * Returns subholders of this Holder
     *
     * @return \Traversable<\Permit\Permission\Holder\HolderInterface>
     **/
    public function getSubHolders()
    {
        return $this->getGroups();
    }

    /**
     * @brief This is a helper to not have guest or system user strings on
     *        youre site
     **/
    public function __get($name)
    {
        return '';
    }

    /**
     * @brief This is a helper to not have guest or system user strings on
     *        youre site
     **/
    public function __call($method, array $params)
    {
        return '';
    }

}