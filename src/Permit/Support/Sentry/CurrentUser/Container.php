<?php namespace Permit\Support\Sentry\CurrentUser;

use Permit\CurrentUser\ContainerInterface;
use Permit\User\UserInterface;
use Cartalyst\Sentry\Sentry;
use InvalidArgumentException;

class Container implements ContainerInterface{

    protected $sentry;


    public function __construct(Sentry $sentry){

        $this->sentry = $sentry;

    }

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\User\UserInterface
     **/
    public function user(){

        return $this->sentry->getUser();

    }

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you shoul simply set a user a second time
     *
     * @param Permit\User\UserInterface $user
     * @param bool $persist Persists the user (in session)
     * @return Permit\User\UserInterface
     **/
    public function setUser(UserInterface $user, $persist=true){

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