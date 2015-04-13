<?php namespace Permit\CurrentUser;


use Permit\User\UserInterface;

interface CanRememberUser
{

    /**
     * Set the user and produce a remember token
     *
     * @param \Permit\User\UserInterface
     * @return \Permit\User\UserInterface
     **/
    public function setAndRemember(UserInterface $user);

}