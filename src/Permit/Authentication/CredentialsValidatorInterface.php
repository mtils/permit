<?php namespace Permit\Authentication;

use Permit\User\UserInterface;

interface CredentialsValidatorInterface{

    /**
     * Check if credentials matches user
     *
     * @param \Permit\User\UserInterface $user
     * @param array $credentials
     * @return bool
     **/
    public function validateCredentials(UserInterface $user, array $credentials);

}