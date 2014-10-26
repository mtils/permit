<?php namespace Permit\Authenticator;

interface AuthenticatorInterface{

    /**
     * @brief Authenticates the user. $credentials dont have to be username
     *        and password.
     *
     * @param array $credentials The (request) params (login/password, whatever)
     * @param string $tryOthers (default: false) Try other methods if first fails
     * @return \Permit\User\UserInterface;
     **/
    public function authenticate(array $credentials, $remember=true, $tryOthers=false);

}