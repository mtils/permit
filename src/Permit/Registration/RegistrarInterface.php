<?php namespace Permit\Registration;

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
     * @return \Permit\Registration\ActivatableUserInterface;
     */
    public function register(array $userData, $activate=false);

    /**
     * @brief Activates the user
     *
     * @param \Permit\Registration\ActivatableUserInterface $user
     * @param array $params The parameters like an activation code
     * @param bool $force Force the activation and skip normal activation validation
     * @return The activated user with groups assigned (or not)
     **/
    public function activate(ActivatableUserInterface $user, array $params=[], $force=false);

    /**
     * @brief Deactivates a user
     *
     * @param \Permit\Registration\ActivatableUserInterface $user
     * @return bool
     **/
    public function deactivate(ActivatableUserInterface $user);

}