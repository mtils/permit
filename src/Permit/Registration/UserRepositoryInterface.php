<?php namespace Permit\Registration;

use Permit\Registration\ActivatableUserInterface;

interface UserRepositoryInterface{

    /**
     * @brief Creates a user with attributes $attributes
     *
     * @param array $attributes
     * @return \Permit\Registration\ActivatableUserInterface
     **/
    public function create(array $attributes);

    /**
     * @brief Saves the user
     *
     * @param Permit\Registration\ActivatableUserInterface $user
     **/
    public function save(ActivatableUserInterface $user);
}