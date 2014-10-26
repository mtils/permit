<?php namespace Permit\CurrentUser;

use Permit\User\UserInterface;
use Permit\Permission\Holder\HolderInterface;
use RuntimeException;
use Permit\Access\CheckerInterface;

class LoginValidator implements LoginValidatorInterface{

    protected $checker;

    public function __construct(CheckerInterface $checker){

        $this->checker = $checker;

    }

    /**
     * @brief Check a user before login. This method throws an exception if
     *        validation fails
     *
     * @param Permit\User\UserInterface $actualUser
     * @param Permit\User\UserInterface $stackedUser (optional)
     * @return void
     *
     * @throws Permit\CurrentUser\LoginAsSuperUserException
     * @throws Permit\CurrentUser\LoginAsSpecialUserException
     * @throws Permit\CurrentUser\UnsufficientPermissionsException
     * @throws Permit\CurrentUser\LessPermissionsThanStackedException
     **/
    public function validateOrFail(UserInterface $actualUser, UserInterface $stackedUser=NULL){

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

        if(!$this->checker->hasAccess($actualUser, $stackedUser)){
            throw new LessPermissionsThanStackedException('You cannot login as a user who has more permissions');
        }

    }

}