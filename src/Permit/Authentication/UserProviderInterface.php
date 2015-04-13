<?php namespace Permit\Authentication;

interface UserProviderInterface
{

    /**
     * Find a user by its credentials. The Provider should not verify
     * the password. If the user was not found, it has to throw an
     * UserNotFoundException.
     * 
     * @param array $credentials
     * @return \Permit\User\UserInterface
     **/
    public function findByCredentials(array $credentials);

}