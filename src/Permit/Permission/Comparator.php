<?php namespace Permit\Permission;

use Permit\Permission\Holder\HolderInterface;
use Permit\Access\CheckerInterface;
use Permit\Permission\PermissionableInterface;

class Comparator{

    protected $tempHolder;

    protected $checker;

    public function __construct(CheckerInterface $checker){

        $this->checker = $checker;

    }

    public function has(HolderInterface $holder){
        $this->tempHolder = $holder;
        return $this;
    }

    public function everyPermissionOf(HolderInterface $holder, $context=PermissionableInterface::ACCESS){

        $hasHolder = $this->tempHolder;
        $this->tempHolder = NULL;

        foreach($holder->permissionCodes() as $code){
            if(!$this->checker->hasAccess($hasHolder, $code, $context)){
                return FALSE;
            }
        }

        return TRUE;

    }

}