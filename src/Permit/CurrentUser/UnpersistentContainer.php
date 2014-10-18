<?php namespace Permit\CurrentUser;

use Permit\Holder\HolderInterface;

class UnpersistentContainer implements ContainerInterface{

    protected $_user;

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\Holder\HolderInterface
     **/
    public function user(){
        return $this->_user;
    }

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you shoul simply set a user a second time
     *
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Persists the user (in session)
     * @return Permit\Holder\HolderInterface
     **/
    public function setUser(HolderInterface $user, $persist=true){
        $this->_user = $user;
        return $this;
    }

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser(){
        $this->_user = NULL;
        return TRUE;
    }
}