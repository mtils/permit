<?php namespace Permit\Holder;

interface HolderInterface{

    /**
     * @brief Access is granted
     * @var int
     **/
    const GRANTED = 1;

    /**
     * @brief Access is not granted. (If code is not present or access = 0)
     * @var int
     **/
    const INHERITED = 0;

    /**
     * @brief Access is denied
     * @var int
     **/
    const DENIED = -1;

    /**
     * @brief returns a unique id for this user
     **/
    public function getAuthId();

    /**
     * @brief Returns the access (self::GRANTED|self::UNAPPROVED|self::DENIED)
     *        for a permission $code (string)
     *
     * @param string $code
     * @return bool
     **/
    public function getPermissionAccess($code);

    /**
     * @brief Sets the access (self::GRANTED|self::UNAPPROVED|self::DENIED)
     *        for the passed permission $code (string)
     *
     * @param string $code The permission code
     * @param int $access self::GRANTED|self::UNAPPROVED|self::DENIED
     * @return void
     **/
    public function setPermissionAccess($code, $access);

    /**
     * @param Returns all permission codes
     *
     * @param bool $inherited
     * @return array
     **/
    public function permissionCodes($inherited=true);

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