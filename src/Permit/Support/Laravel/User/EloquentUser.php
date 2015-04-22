<?php namespace Permit\Support\Laravel\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Contracts\Auth\Authenticatable
use Illuminate\Contracts\Auth\CanResetPassword as ResetPasswordInterface;
use Illuminate\Auth\Passwords\CanResetPassword;

use Permit\User\UserInterface as PermitUserInterface;
use Permit\Support\Laravel\Permission\Holder\EloquentJsonPermissionsTrait;

class EloquentUser extends Model implements Authenticatable,
                                            ResetPasswordInterface,
                                            PermitUserInterface
{

    use UserTrait;
    use CanResetPassword;
    use EloquentUserTrait;

}