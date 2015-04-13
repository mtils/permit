<?php namespace Permit\Authentication;

interface GuardInterface extends AuthenticatorInterface{

    /**
     * De-authenticates the user in the system
     *
     * @return bool
     **/
    public function logout();

}