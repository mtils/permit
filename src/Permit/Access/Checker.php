<?php namespace Permit\Access;

use Permit\User\UserInterface;

class Checker implements CheckerInterface{

    /**
     * @brief Returns if holder has acces to $resourceOrCode within $context
     *
     * @param Permit\User\UserInterface $holder The Holder of permission codes
     * @param mixed $resource The resource
     * @param string $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $holder, $resource, $context='access'){

        if($holder->isSuperUser()){
            return true;
        }
        return false;
    }

}