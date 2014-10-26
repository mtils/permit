<?php namespace Permit\CurrentUser;

use Permit\User\UserInterface;

interface ContainerInterface{

    /**
     * @brief Retrieve the current user.
     *
     * @return \Permit\User\UserInterface
     **/
    public function user();

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you shoul simply set a user a second time
     *
     * @param \Permit\User\UserInterface $user
     * @param bool $persist Persists the user (in session)
     * @return Permit\User\UserInterface
     **/
    public function setUser(UserInterface $user, $persist=true);

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser();

}