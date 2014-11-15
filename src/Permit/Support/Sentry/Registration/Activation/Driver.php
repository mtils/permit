<?php namespace Permit\Support\Sentry\Registration\Activation;

use Permit\User\UserInterface;
use Permit\Registration\Activation\DriverInterface;
use Permit\Registration\Activation\ActivationDataInvalidException;
use Permit\User\UserNotFoundException;

use Cartalyst\Sentry\Users\UserInterface as SentryUserInterface;
use Cartalyst\Sentry\Users\UserAlreadyActivatedException;
use Cartalyst\Sentry\Users\ProviderInterface;
use Cartalyst\Sentry\Users\UserNotFoundException as SentryNotFoundException;

use InvalidArgumentException;


class Driver implements DriverInterface{

    protected $userProvider;

    public function __construct(ProviderInterface $userProvider){

        $this->userProvider = $userProvider;

    }

    /**
     * @brief Reserves a user for activation but does not activate him.
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function reserveActivation(UserInterface $user){

        $this->checkForSentryInterface($user);

        // Sentry automatically saves the user...
        if($activationCode = $user->getActivationCode()){
            return TRUE;
        }

        return FALSE;

    }

    /**
     * Find a user by activationdata. If you have a simple
     * activation code based system you would pass ['code'=>$activationCode]
     *
     * This method should not throw any domain logic exceptions.
     * 
     * Its part of the RegistrarInterface to deceide if an exception has
     * to be thrown if the user is already activated or other domain
     * specific reasons.
     *
     * If activation data is invalid or the user is not found it must throw
     * an exception
     *
     * @throws Permit\Registration\Activation\ActivationDataInvalidException
     * @throws Permit\User\UserNotFoundException
     *
     * @param array $activationData The activation params
     * @return \Permit\User\UserInterface
     **/
    public function getUserByActivationData(array $activationData){

        $this->checkActivationData($activationData);

        try{

            $user = $this->userProvider->findByActivationCode($activationData['code']);

            $this->checkForSentryInterface($user);

            return $user;

        }
        catch(SentryNotFoundException $e){

            throw new UserNotFoundException();

        }

        return $user;

    }

    /**
     * Activate the user, no matter how or why
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function activate(UserInterface $user){

        $this->checkForSentryInterface($user);

        try{

            return $user->attemptActivation($user->getActivationCode());

        }
        catch(UserAlreadyActivatedException $e){

            return false;

        }


    }

    /**
     * Return if the user is activated
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function isActivated(UserInterface $user){

        $this->checkForSentryInterface($user);

        return $user->isActivated();

    }

    /**
     * An assoziative array (key=>value) of activation data
     * (code=>'dadsli874rwlefdusdo7izh')
     *
     * @param \Permit\User\UserInterface $user
     * @return array
     **/
    public function getActivationData(UserInterface $user){

        $this->checkForSentryInterface($user);

        return ['code' => $user->activation_code];

    }

    /**
     * Check if activation data is valid
     *
     * @throws Permit\Registration\Activation\ActivationDataInvalidException
     *
     * @param array $activationData
     * @return void
     **/
    protected function checkActivationData(array $activationData){

        if( !isset($activationData['code']) || strlen($activationData['code']) < 16){
            throw new ActivationDataInvalidException();
        }

    }

    protected function checkForSentryInterface(UserInterface $user){
        if(!$user instanceof SentryUserInterface){
            throw new InvalidArgumentException("User has to be a valid sentry user instance");
        }
    }

}