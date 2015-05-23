<?php namespace Permit\Registration;

use DomainException;
use InvalidArgumentException;
use RuntimeException;

use Permit\User\UserInterface;
use Permit\Registration\ActivatableInterface as ActivatableUser;

class UserAlreadyActivatedException extends DomainException{}
class UnactivatebleUserException extends InvalidArgumentException{}
class ActivationFailedException extends RuntimeException{}

/**
 * @brief The registrar registers, activates and deactivates users
 **/
interface RegistrarInterface{

    /**
     * Registers a user. If you pass true as the second argument the user will
     * instantly be activated. If you pass a closure this closure will be called
     * with the activation token and the user to send a mail or so.
     *
     * @example Registrar::register($credentials, true) => Register and activate
     * @example Registrar::register($credentials, false) => Register unactivated (default)
     * @example Registrar::register($credentials, function($user, $token){})
     *          Register an unactivated user, create a token and call closure
     *
     * @param  array  $userData
     * @param  bool|callable $activation (default:false)
     * @return \Permit\Registration\ActivatableInterface
     */
    public function register(array $userData, $activation=false);

    /**
     * Try to activate an user by activationParams like a code or many
     *
     * @param array $activationParams
     * @return \Permit\Registration\ActivatableInterface
     **/
    public function attemptActivation(array $activationParams);

    /**
     * Activates the user.
     *
     *
     * @param \Permit\Registration\ActivatableInterface $user
     * @param bool $enforceActivationProcess Force or bybass activation process
     * @return The activated user with groups assigned (or not)
     **/
    public function activate(ActivatableUser $user);

    /**
     * Returns if user $user is activated
     *
     * @param \Permit\Registration\ActivatableInterface $user
     * @return bool
     **/
    public function isActivated(ActivatableUser $user);

}