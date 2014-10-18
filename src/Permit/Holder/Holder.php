<?php namespace Permit\Holder;

class Holder implements HolderInterface{

    protected $authId;

    protected $permissionCodes = [];

    protected $guest;

    protected $system;

    /**
     * @brief returns a unique id for this user
     **/
    public function getAuthId(){
        return $this->authId;
    }

    public function setAuthId($authId){
        $this->authId = $authId;
    }

    /**
     * @brief Returns the access (self::GRANTED|self::UNAPPROVED|self::DENIED)
     *        for a permission $code (string)
     *
     * @param string $code
     * @return bool
     **/
    public function getPermitAccess($code){
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
    public function setPermitAccess($code, $access){
        $this->permissionCodes[$code] = $access;
    }

    /**
     * @param Returns all permission codes
     *
     * @param bool $inherited
     * @return array
     **/
    public function permissionCodes($inherited=true){
        return array_keys($this->permissionCodes);
    }

    /**
     * @brief Returns if the user is nobody. Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isGuest(){
        return $this->guest;
    }

    public function setIsGuest($isGuest){
        $this->guest = $isGuest;
    }

    /**
     * @brief Returns if the user is the system (like cron). Makes sense in Situation where
     *        you like to have a user or its id.
     *
     * @return bool
     **/
    public function isSystem(){
        return $this->system;
    }

    public function setIsSystem($isSystem){
        $this->system = $isSystem;
    }

    /**
     * @brief Returns if the user is the superuser
     *
     * @return bool
     **/
    public function isSuperUser(){
        return $this->isSystem();
    }

    /**
     * @brief This is a helper to not have guest or system user strings on
     *        youre site
     **/
    public function __get($name){
        return '';
    }

    /**
     * @brief This is a helper to not have guest or system user strings on
     *        youre site
     **/
    public function __call($method, array $params){
        return '';
    }

}