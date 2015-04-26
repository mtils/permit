<?php namespace Permit\Registration;

use Permit\User\UserInterface;
use Permit\User\ProviderInterface;

interface UserRepositoryInterface extends ProviderInterface
{

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
     * @return bool
     **/
    public function save(ActivatableInterface $user);

}