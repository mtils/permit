<?php namespace Permit\Checker;

use Permit\Holder\HolderInterface;
use Permit\PermissionableInterface;

class Checker implements CheckerInterface{

    /**
     * @brief Returns if holder has acces to $resourceOrCode within $context
     *
     * @param Permit\Holder\HolderInterface $holder The Holder of permission codes
     * @param string|Permit\PermissionableInterface $resourceOrCode The resource
     * @param int $context (optional)
     * @return bool
     **/
    public function hasAccess(HolderInterface $holder, $resourceOrCode, $context=PermissionableInterface::ACCESS){

        if($holder->isSuperUser()){
            return true;
        }
        if($resourceOrCode instanceof PermissionableInterface){
            $resourceOrCode = $resourceOrCode->requiredPermit($context);
        }

        return ($holder->getPermitAccess($resourceOrCode) === HolderInterface::GRANTED);

    }

}