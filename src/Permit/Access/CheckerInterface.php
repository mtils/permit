<?php namespace Permit\Access;

use Permit\User\UserInterface;

interface CheckerInterface{

    /**
     * Returns if user has access to $resource within $context.
     * The checker must return true if it knows that access to $resource
     * is granted.
     * It must return false if it knows that access to $resource is denied.
     * If it has no idea what do do with $resource it has to return null.
     * This allows a pluggable chain of responsibility for mixed auth systems
     * (Role-based, Group-based, Permission-based, bitmasks, ...)
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool|null
     **/
    public function hasAccess(UserInterface $user, $resource, $context='access');

}