<?php namespace Permit\Access;

use Permit\User\UserInterface;

class FakeAssigner implements AssignerInterface{

    /**
     * @brief Assigns the roles/groups/permissions to user $user
     *        Typically this is done while activating/registering a user
     *
     * @param Permit\User\UserInterface $user
     * @param $forActivation (default: true)
     * @return bool
     **/
    public function assignAccessRights(UserInterface $user, $forActivation=true){}

}