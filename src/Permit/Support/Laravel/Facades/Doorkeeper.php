<?php namespace Permit\Support\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Doorkeeper extends Facade{

    protected static function getFacadeAccessor(){
        return 'Permit\Doorkeeper\DoorkeeperInterface';
    }

    public static function find($id)
    {
        return static::getFacadeRoot()->findByGroupId($id);
    }
}