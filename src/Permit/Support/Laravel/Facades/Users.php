<?php namespace Permit\Support\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Users extends Facade{

    protected static function getFacadeAccessor(){
        return 'Permit\Registration\UserRepositoryInterface';
    }

    public static function find($id)
    {
        return static::getFacadeRoot()->retrieveByAuthId($id);
    }
}