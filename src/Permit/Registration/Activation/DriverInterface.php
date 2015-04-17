<?php namespace Permit\Registration\Activation;

use UnderflowException;
use UnexpectedValueException;

use Permit\User\UserInterface;

class ActivationDataInvalidException extends UnexpectedValueException{}

/**
 * @brief An Activation\Driver is a class which manages the activation.
 *        If it is activation code based you would store an activation code
 *        somewhere at reserveActivation().
 *        Then if the user activates itself you would call attemptActivation
 *        and the driver should find this activation code and compares it.
 *        Because the activation normally happens max. one time per user I
 *        would suggest to not put it into your user table
 **/
interface DriverInterface{

    /**
     * @brief Reserves a user for activation but does not activate him
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function reserveActivation(UserInterface $user);

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
     * If activation data is invalid or the user is not found it should throw
     * an exception
     *
     * @throws Permit\Registration\Activation\ActivationDataInvalidException
     * @throws Permit\User\UserNotFoundException
     *
     * @param array $activationData The activation params
     * @return \Permit\User\UserInterface
     **/
    public function getUserByActivationData(array $activationData);

    /**
     * Activate the user, no matter how or why. The $save parameter is to
     * determine that the activation will be saved.
     *
     * @param \Permit\User\UserInterface $user
     * @param bool $save (default:true)
     * @return bool
     **/
    public function activate(UserInterface $user, $save=true);

    /**
     * Return if the user is activated
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function isActivated(UserInterface $user);

    /**
     * An assoziative array (key=>value) of activation data
     * (code=>'dadsli874rwlefdusdo7izh')
     *
     * @param \Permit\User\UserInterface $user
     * @return array
     **/
    public function getActivationData(UserInterface $user);


}