<?php namespace Permit\Authentication;


interface AuthenticatorInterface
{

    /**
     * Authenticates the user. $credentials dont have to be username
     * and password. Has to throw a UserNotFoundException if the user was
     * not found
     *
     * @param array $credentials The (request) params (login/password, whatever)
     * @param $remember Create a remember token
     * @return \Permit\User\UserInterface;
     * @throws \Permit\User\UserNotFoundException
     **/
    public function authenticate(array $credentials, $remember=true);

    /**
     * Logges the user out and returns it
     *
     * @return \Permit\User\UserNotFoundException
     **/
    public function logout();

}