<?php namespace Permit\CurrentUser;

use Permit\Holder\HolderInterface;
use RuntimeException;

class LoginValidator implements LoginValidatorInterface{

    /**
     * @brief Check a user before login. This method throws an exception if
     *        validation fails
     *
     * @param Permit\Holder\HolderInterface $actualUser
     * @param Permit\Holder\HolderInterface $stackedUser (optional)
     * @return void
     *
     * @throws Permit\CurrentUser\LoginAsSuperUserException
     * @throws Permit\CurrentUser\LoginAsSpecialUserException
     * @throws Permit\CurrentUser\UnsufficientPermissionsException
     * @throws Permit\CurrentUser\LessPermissionsThanStackedException
     **/
    public function validateOrFail(HolderInterface $actualUser, HolderInterface $stackedUser=NULL){

        if($actualUser->isGuest() || $actualUser->isSystem()){
            throw new LoginAsSpecialUserException('You can\' login as a special user');
        }

        if($stackedUser === NULL){
            return;
        }

        if($stackedUser->isGuest() || $stackedUser->isSystem()){
            throw new LoginAsSpecialUserException('You can\' login as a special user');
        }

        if($actualUser->getAuthId() == $stackedUser->getAuthId()){
            throw new LoginAsSameUserException('You can\'t login as you');
        }

        if($stackedUser->isSuperUser()){
            throw new LoginAsSuperUserException('You can\' login as a superuser');
        }

        // If the actual user is superuser allow
        if($actualUser->isSuperUser()){
            return;
        }

        // Check if stackedUser has any permission actualUser has not
        foreach($stackedUser->permissionCodes() as $permCode){

            if($stackedUser->getPermissionAccess($permCode) === HolderInterface::GRANTED){

                if($actualUser->getPermissionAccess($permCode) !== HolderInterface::GRANTED){

                    throw new LessPermissionsThanStackedException('You cannot login as a user who has more permissions');

                }

            }

        }

    }

}