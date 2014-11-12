<?php namespace Permit\Registration;

use Permit\User\UserInterface;

/**
 * @brief The registrar registers, activates and deactivates users
 **/
interface RegistrarInterface{

    /**
     * @brief Registers a user. If activation is forced the user will instantly be
     *        activated after creation
     *
     * @param  array  $userData
     * @param  bool   $activate (default:false)
     * @return \Permit\User\UserInterface
     */
    public function register(array $userData, $activate=false);

    /**
     * @brief Activates the user
     *
     * @param Permit\User\UserInterface $user
     * @param array $params The parameters like an activation code
     * @param bool $force Force the activation and skip normal activation validation
     * @return The activated user with groups assigned (or not)
     **/
    public function activate(UserInterface $user, array $params=[], $force=false);

}