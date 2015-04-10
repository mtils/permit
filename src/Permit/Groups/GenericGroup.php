<?php namespace Permit\Groups;

use Permit\Permission\Holder\HolderInterface as PermissionHolder;
use Permit\Permission\Holder\GenericHolderTrait as PermissionHolderTrait;

class GenericGroup implements GroupInterface, PermissionHolder{

    use PermissionHolderTrait;

    protected $groupId;

    /**
     * Returns the group's ID.
     *
     * @return mixed
     */
    public function getGroupId(){
        return $this->groupId;
    }

    public function setGroupId($id){
        $this->groupId = $id;
    }

}