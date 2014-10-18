<?php namespace Permit\CurrentUser;

use Permit\Holder\HolderInterface;

interface ContainerInterface{

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\Holder\HolderInterface
     **/
    public function user();

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you shoul simply set a user a second time
     *
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Persists the user (in session)
     * @return Permit\Holder\HolderInterface
     **/
    public function setUser(HolderInterface $user, $persist=true);

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser();

}