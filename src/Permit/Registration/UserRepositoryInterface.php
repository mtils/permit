<?php namespace Permit\Registration;

use Permit\User\UserInterface;

interface UserRepositoryInterface{

    /**
     * @brief Creates a user with attributes $attributes
     *
     * @param array $attributes
     * @param bool $activate (default: true)
     * @return \Permit\User\UserInterface
     **/
    public function create(array $attributes, $activate=true);

    /**
     * @brief Saves the user
     *
     * @param \Permit\User\UserInterface $user
     **/
    public function save(UserInterface $user);
}