<?php namespace Permit\Support\Laravel\Authentication;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * This class is used to log the last login in the users table
 **/
class UserModelLastLoginWriter
{

    public $lastLoginAttribute = 'last_login';

    public $updateTimestamps = false;

    public function __invoke(Model $user, $remember)
    {
        $user->{$this->lastLoginAttribute} = $user->freshTimestamp();

        $options = $this->updateTimestamps ? [] : ['timestamps'=>false];
        $user->save($options);
    }

}