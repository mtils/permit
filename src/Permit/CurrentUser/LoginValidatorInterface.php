<?php namespace Permit\CurrentUser;

use Permit\Holder\HolderInterface;
use RuntimeException;

class LoginAsRequiresActualUserException extends RuntimeException{};
class LoginAsSameUserException extends RuntimeException{};
class LoginAsSuperUserException extends RuntimeException{};
class LoginAsSpecialUserException extends RuntimeException{};
class UnsufficientPermissionsException extends RuntimeException{};
class LessPermissionsThanStackedException extends RuntimeException{};

interface LoginValidatorInterface{

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
    public function validateOrFail(HolderInterface $actualUser, HolderInterface $stackedUser=NULL);

}