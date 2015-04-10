<?php namespace Permit\Permission\Holder;

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
     * @brief Returns the access (self::GRANTED|self::INHERITED|self::DENIED)
     *        for a permission $code (string)
     *
     * @param string $code
     * @return int
     **/
    public function getPermissionAccess($code);

    /**
     * @brief Sets the access (self::GRANTED|self::INHERITED|self::DENIED)
     *        for the passed permission $code (string)
     *
     * @param string $code The permission code
     * @param int $access self::GRANTED|self::INHERITED|self::DENIED
     * @return void
     **/
    public function setPermissionAccess($code, $access);

    /**
     * @param Returns all permission codes (indexed array)
     *
     * @return array
     **/
    public function permissionCodes();

}