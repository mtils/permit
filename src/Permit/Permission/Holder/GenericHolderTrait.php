<?php namespace Permit\Permission\Holder;

trait GenericHolderTrait{

    protected $permissionCodes = [];

    /**
     * @brief Returns the access (self::GRANTED|self::UNAPPROVED|self::DENIED)
     *        for a permission $code (string)
     *
     * @param string $code
     * @return bool
     **/
    public function getPermissionAccess($code){
        if(isset($this->permissionCodes[$code])){
            return $this->permissionCodes[$code];
        }
        return self::INHERITED;
    }

    /**
     * @brief Sets the access (self::GRANTED|self::UNAPPROVED|self::DENIED)
     *        for the passed permission $code (string)
     *
     * @param string $code The permission code
     * @param int $access self::GRANTED|self::UNAPPROVED|self::DENIED
     * @return void
     **/
    public function setPermissionAccess($code, $access){
        $this->permissionCodes[$code] = $access;
    }

    /**
     * @param Returns all permission codes (numeric array)
     *
     * @return array
     **/
    public function permissionCodes(){
        return array_keys($this->permissionCodes);
    }

}