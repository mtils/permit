<?php namespace Permit\Permission\Holder;

use Permit\Permission\PermissionableInterface;

/**
 * @brief This trait makes a user and group a permissionable object
 *        If the system needs to know if user x can access user y
 *        or access group y the required permissions to access it are all
 *        the permissions the target user or group contains
 **/
trait PermissionableHolderTrait{

    /**
     * @brief Returns the required permission codes to access this
     *        object inside a $context context
     *
     * @param int $context A context to allow different cases
     * @return string A permission code
     **/
    public function requiredPermissionCodes($context=PermissionableInterface::ACCESS){

        $codes = $this->collectAllowedPermissionCodes($this);

        if ($this instanceof NestedHolderInterface) {
            $codes = array_merge($this->collectNestedHolderPermissions());
        }

        return $codes;

    }

    protected function collectNestedHolderPermissions()
    {
        $nestedPermissions = [];

        foreach ($this->getSubHolders() as $subHolder) {
            $nestedPermissions = array_merge($this->collectAllowedPermissionCodes($subHolder));
        }

        return $nestedPermissions;
    }

    protected function collectAllowedPermissionCodes(HolderInterface $holder)
    {
        $codes = [];
        foreach ($holder->permissionCodes() as $code) {
            if($holder->getPermissionAccess($code) == 1){
                $codes[] = $code;
            }
        }
        return $codes;
    }

}