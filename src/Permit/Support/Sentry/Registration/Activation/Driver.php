<?php namespace Permit\Support\Sentry\Registration\Activation;

use Permit\User\UserInterface;
use Permit\Registration\Activation\DriverInterface;

use Cartalyst\Sentry\Users\UserInterface as SentryUserInterface;

use InvalidArgumentException;

class Driver implements DriverInterface{

    /**
     * @brief Reserves a user for activation but does not activate him
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function reserveActivation(UserInterface $user){

        $this->checkForSentryInterface($user);

        // Sentry automatically saves the user...
        $user->getActivationCode();

        return TRUE;

    }

    /**
     * @brief Try to activate the user with the given params. It depends on
     *        the implementation what the params are. If you have a simple
     *        activation code based system you would pass [$activationCode]
     *
     * @param \Permit\User\UserInterface $user
     * @param array $params (optional) The activation params
     * @return bool
     **/
    public function attemptActivation(UserInterface $user, array $params=[]){

        $this->checkForSentryInterface($user);

        $user->attemptActivation($params[0]);

    }

    protected function checkForSentryInterface(UserInterface $user){
        if(!$user instanceof SentryUserInterface){
            throw new InvalidArgumentException("User has to be a valid sentry user instance");
        }
    }

}