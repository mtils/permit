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
     * Registers a user. If activation is forced the user will instantly be
     * activated after creation
     *
     * @param  array  $userData
     * @param  bool   $activate (default:false)
     * @return \Permit\Registration\ActivatableInterface
     */
    public function register(array $userData, $activate=false);

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