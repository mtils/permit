<?php namespace Permit\Support\Laravel\CurrentUser;


use Illuminate\Auth\Guard;

use Permit\CurrentUser\ContainerInterface;
use Permit\CurrentUser\CanRememberUser;
use Permit\User\UserInterface;

class GuardContainer implements ContainerInterface, CanRememberUser
{

    /**
     * @var \Illuminate\Auth\Guard
     **/
    protected $guard;

    public function __construct(Guard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * @brief Retrieve the current user.
     *
     * @return \Permit\User\UserInterface
     **/
    public function user()
    {
        return $this->guard->user();
    }

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you should simply set a user a second time
     *
     * @param \Permit\User\UserInterface $user
     * @param bool $persist Persists the user (in session)
     * @return Permit\User\UserInterface
     **/
    public function setUser(UserInterface $user, $persist=true)
    {
        if(!$persist){
            $this->guard->setUser($user);
            return $user;
        }

        $this->guard->login($user, false);

        return $user;

    }

    /**
     * Set the user and produce a remember token
     *
     * @param \Permit\User\UserInterface
     * @return \Permit\User\UserInterface
     **/
    public function setAndRemember(UserInterface $user)
    {
        $this->guard->login($user, true);
        return $user;
    }

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser()
    {
        $this->guard->logout();
        return true;
    }

}