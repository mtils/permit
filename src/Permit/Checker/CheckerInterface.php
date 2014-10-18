<?php namespace Permit\Checker;

use Permit\Holder\HolderInterface;

interface CheckerInterface{

    /**
     * @brief Returns if holder has acces to $resourceOrCode within $context
     *
     * @param Permit\Holder\HolderInterface $holder The Holder of permission codes
     * @param string|Permit\PermissionableInterface $resourceOrCode The resource
     * @param int $context (optional)
     * @return bool
     **/
    public function hasAccess(HolderInterface $holder, $resourceOrCode, $context=PermissionableInterface::ACCESS);

}