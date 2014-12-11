<?php namespace Permit\Permission;

use Permit\Access\CheckerInterface;
use Permit\User\UserInterface;
use Permit\Permission\Holder\HolderInterface;
use Permit\Permission\PermissionableInterface;

use InvalidArgumentException;

class AccessChecker implements CheckerInterface{

    /**
     * @brief Returns if user has access to $resource within $context
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='default'){

        return $this->hasPermissionAccess($user, $resource, $context);

    }

    /**
     * @brief Returns if holder has acces to $resourceOrCode within $context
     *
     * @param Permit\Permission\Holder\HolderInterface $holder The Holder of permission codes
     * @param string|Permit\Permission\PermissionableInterface|array $resource The resource
     * @param int $context (optional)
     * @return bool|null
     **/
    public function hasPermissionAccess(HolderInterface $holder, $resourceOrCode, $context=PermissionableInterface::ACCESS){

        if($holder->isSuperUser()){
            return true;
        }

        if($resourceOrCode instanceof PermissionableInterface){
            $codes = $resourceOrCode->requiredPermissionCodes($context);
        }
        elseif(is_array($resourceOrCode)){
            $codes = $resourceOrCode;
        }
        elseif(is_string($resourceOrCode)){
            $codes = [$resourceOrCode];
        }
        else{
            return null;
        }

        foreach($codes as $code){
            if($holder->getPermissionAccess($code) != HolderInterface::GRANTED){
                return false;
            }
        }

        return true;

    }

}