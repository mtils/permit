<?php namespace Permit\Support\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Groups extends Facade{

    protected static function getFacadeAccessor(){
        return 'Permit\Groups\GroupRepositoryInterface';
    }

    public static function find($id)
    {
        return static::getFacadeRoot()->findByGroupId($id);
    }
}