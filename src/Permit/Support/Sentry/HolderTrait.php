<?php namespace Permit\Support\Sentry;

use Permit\Holder\HolderInterface;

trait HolderTrait{

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
    public function getPermitAccess($code){

        $codeParts = explode('.',$code);
        $prefix = '';

        if( count($codeParts) > 1){
            array_pop($codeParts);
            $prefix = implode('.',$codeParts);
        }

        foreach($this->getMergedPermissions() as $codeKey=>$access){

            if($codeKey == $code){
                return $access;
            }

            if(substr($codeKey,-1) == '*'){
                if(substr($codeKey,0,-1) == $prefix){
                    return $access;
                }
            }
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
    public function setPermitAccess($code, $access){

        $permissions = $this->permissions;
        $permissions[$code] = $access;
        $this->permissions = $permissions;
    }

    /**
     * @param Returns all permission codes
     *
     * @param bool $inherited
     * @return array
     **/
    public function permissionCodes($inherited=true){

        if($inherited){
            return array_keys($this->getMergedPermissions());
        }

        return $this->permissions;

    }

}