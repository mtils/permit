<?php namespace Permit\Support\Sentry;

use Permit\CurrentUser\ContainerInterface;
use Permit\Holder\HolderInterface;
use Cartalyst\Sentry\Sentry;
use InvalidArgumentException;

class CurrentUserContainer implements ContainerInterface{

    protected $sentry;


    public function __construct(Sentry $sentry){

        $this->sentry = $sentry;

    }

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\Holder\HolderInterface
     **/
    public function user(){

        return $this->sentry->getUser();

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

        if(!$persist){
            $this->sentry->setUser($user);
            return $user;
        }

        if($user->isGuest() || $user->isSystem()){
            throw new InvalidArgumentException('You can\'t put guests or system into session');
        }

        $this->sentry->login($user);
        return $user;

    }

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser(){
        $this->sentry->logout();
    }

}