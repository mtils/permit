<?php namespace Permit\Registration\Activation;

use Permit\User\UserInterface;

/**
 * @brief An Activation\Driver is a class which manages the activation.
 *        If it is activation code based you would store an activation code
 *        somewhere at reserveActivation().
 *        Then if the user activates itself you would call attemptActivation
 *        and the driver should find this activation code and compares it.
 *        Because the activation normally happens max. one time per user I
 *        would suggest to not put into your user table
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
     * @brief Try to activate the user with the given params. It depends on
     *        the implementation what the params are. If you have a simple
     *        activation code based system you would pass [$activationCode]
     *
     * @param \Permit\User\UserInterface $user
     * @param array $params (optional) The activation params
     * @return bool
     **/
    public function attemptActivation(UserInterface $user, array $params=[]);

}