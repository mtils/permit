<?php namespace Permit\Support\Laravel\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\Reminders\RemindableTrait;

use Permit\User\UserInterface as PermitUserInterface;
use Permit\Support\Laravel\Permission\Holder\EloquentJsonPermissionsTrait;

class EloquentUser extends Model implements UserInterface, RemindableInterface,
                                            PermitUserInterface
{

    use UserTrait;
    use RemindableTrait;
    use EloquentUserTrait;

}