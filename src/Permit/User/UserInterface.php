<?php namespace Permit\User;

interface UserInterface{

    /**
     * @brief returns a unique id for this user
     **/
    public function getAuthId();

    /**
     * @brief Returns if the user is nobody. Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isGuest();

    /**
     * @brief Returns if the user is the system (like cron). Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isSystem();

    /**
     * @brief Returns if the user is the superuser
     *
     * @return bool
     **/
    public function isSuperUser();

}