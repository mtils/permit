<?php namespace Permit\Registration;

use Permit\User\UserInterface;

/**
 * Hook this class into login hools to allow throttle checks on login
 **/
class ChecksActivationOnLogin
{

    /**
     * Hook this into Authenticator::whenAttempted
     *
     * @param Permit\User\UserInterface $user
     * @param array $credentials
     * @param bool $remember
     * @return void
     * @throws Permit\Registration\UserNotActivatedException
     **/
    public function checkActivation(UserInterface $user, array $credentials, $remember)
    {
        if ($user->isActivated()) {
            return;
        }
        throw new UserNotActivatedException("User is not activated");
    }

}