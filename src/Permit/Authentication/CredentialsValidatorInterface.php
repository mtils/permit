<?php namespace Permit\Authentication;

use Permit\User\UserInterface;

interface CredentialsValidatorInterface
{

    /**
     * Check if credentials matches user. Return null if the validator dont know
     * how to validate the given credentials, so that another could try it.
     * true means the validator knows the credentials are valid, false means
     * the validator knows they are valid. null means he has no clue
     *
     * @param \Permit\User\UserInterface $user
     * @param array $credentials
     * @return bool|null
     **/
    public function validateCredentials(UserInterface $user, array $credentials);

}