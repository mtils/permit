<?php namespace Permit\Support\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class Permissions extends Facade{

    protected static function getFacadeAccessor(){
        return 'permission-repository';
    }
}