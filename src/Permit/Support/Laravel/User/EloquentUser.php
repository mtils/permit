<?php namespace Permit\Support\Laravel\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword as ResetPasswordInterface;
use Illuminate\Auth\Passwords\CanResetPassword;

use Permit\User\UserInterface as PermitUserInterface;
use Permit\Permission\Holder\HolderInterface;
use Permit\Permission\Holder\NestedHolderInterface;
use Permit\Support\Laravel\Groups\EloquentHolderTrait;
use Permit\Support\Laravel\Permission\Holder\EloquentJsonPermissionsTrait;
use Permit\Support\Laravel\Registration\ActivatableByDate;
use Permit\Registration\ActivatableInterface;

class EloquentUser extends Model implements Authenticatable,
                                            ResetPasswordInterface,
                                            PermitUserInterface,
                                            ActivatableInterface,
                                            HolderInterface,
                                            NestedHolderInterface
{

    use UserTrait;
    use CanResetPassword;
    use EloquentUserTrait;
    use EloquentHolderTrait;
    use EloquentJsonPermissionsTrait;
    use ActivatableByDate;

}