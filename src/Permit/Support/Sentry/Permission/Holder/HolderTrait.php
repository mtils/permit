<?php namespace Permit\Support\Sentry\Permission\Holder;


use Permit\Permission\Holder\HolderInterface;


trait HolderTrait
{

    /**
     * @brief returns a unique id for this user
     * @see Cartalyst\Sentry\Users\UserInterface::getId()
     **/
    public function getAuthId(){
        return $this->getId();
    }

    /**
     * @brief Returns the access (self::GRANTED|self::UNAPPROVED|self::DENIED)
     *        for a permission $code (string)
     *
     * @param string $code
     * @return bool
     **/
    public function getPermissionAccess($code)
    {

        $permissions = $this->getPermissions();

        if(isset($permissions[$code])){
            return $permissions[$code];
        }

        return HolderInterface::INHERITED;
    }

    /**
     * @brief Sets the access (self::GRANTED|self::UNAPPROVED|self::DENIED)
     *        for the passed permission $code (string)
     *
     * @param string $code The permission code
     * @param int $access self::GRANTED|self::UNAPPROVED|self::DENIED
     * @return void
     **/
    public function setPermissionAccess($code, $access)
    {

        $permissions = $this->permissions;
        $permissions[$code] = $access;
        $this->permissions = $permissions;
    }

    /**
     * @param Returns all permission codes
     *
     * @return array
     **/
    public function permissionCodes()
    {
        return array_keys($this->getPermissions());
    }

}