<?php namespace Permit\Support\Laravel\User;

trait EloquentUserTrait{

    protected $_isGuest;

    protected $_isSystem;

    protected $_isSuperUser;

    /**
     * @brief returns a unique id for this user
     **/
    public function getAuthId()
    {
        return $this->getKey();
    }

    /**
     * @brief Returns if the user is nobody. Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isGuest()
    {
        return $this->_isGuest;
    }

    public function setIsGuest($isGuest){
        $this->_isGuest = $isGuest;
        return $this;
    }

    /**
     * @brief Returns if the user is the system (like cron). Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isSystem()
    {
        return $this->_isSystem;
    }

    public function setIsSystem($isSystem){
        $this->_isSystem = $isSystem;
        return $this;
    }

    /**
     * @brief Returns if the user is the superuser
     *
     * @return bool
     **/
    public function isSuperUser()
    {
        return $this->_isSuperUser;
    }

    public function setIsSuperUser($isSuperUser){
        $this->_isSuperUser = $isSuperUser;
        return $this;
    }

}