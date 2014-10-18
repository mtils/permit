<?php namespace Permit\CurrentUser;

use Permit\Holder\HolderInterface;

interface DualContainerInterface extends ContainerInterface{

    const BOTH = 0;

    const ACTUAL = 1;

    const STACKED = 2;


    /**
     * @brief Returns the user which was acutally logged in, no matter if he
     *        was logged in as some other user
     *
     * @return Permit\Holder\HolderInterface
     **/
    public function actualUser();

    /**
     * @brief Sets the actual user
     *
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Permist the user (in session)
     * @return void
     **/
    public function setActualUser(HolderInterface $user, $persist=true);

    /**
     * @brief Return the user currently set by an (admin) to be logged in as.
     *        If the user didnt login as someone different it returns null
     *
     * @return Permit\Holder\HolderInterface|null
     **/
    public function stackedUser();

    /**
     * @brief Set the stacked user which is the user an admin wants to login
     *        as
     *
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Permist the user (in session)
     * @return void
     **/
    public function setStackedUser(HolderInterface $user, $persist=true);

    /**
     * @brief Force the user returned by user() to be self::ACTUAL
     * 
     * @param bool $force
     **/
    public function forceActual($force=TRUE);

    /**
     * @brief Returns if the user returned by user() is the actual user
     *        which performed the login process
     *
     * @return bool
     **/
    public function isActual();

    /**
     * @brief Resets the container. Resets the current user and actual user
     *
     * @param int $type (optional) Which user to reset
     * @return void
     **/
    public function reset($type=self::BOTH);

}