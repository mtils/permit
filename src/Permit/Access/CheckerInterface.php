<?php namespace Permit\Access;

use Permit\User\UserInterface;

interface CheckerInterface{

    /**
     * @brief Returns if user has access to $resource within $context
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='access');

}