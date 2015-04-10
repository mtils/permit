<?php namespace Permit\Permission;

trait GenericPermissionableTrait{

    protected $requiredCodes = [];

    /**
     * @brief Returns the required permission codes to access this
     *        object inside a $context context
     *
     * @param int $context A context to allow different cases
     * @return string A permission code
     **/
    public function requiredPermissionCodes($context=PermissionableInterface::ACCESS){
        return $this->requiredCodes;
    }

    public function setRequiredPermissionCodes(array $codes){
        $this->requiredCodes = $codes;
    }

}