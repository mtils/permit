<?php namespace Permit\Permission\Holder;

/**
 * A nested PermissionHolder has many other holders as sub object
 * All other holders are also HolderInterface. The permissions of
 * all subholders will be merged with the permissions of the main
 * holder. A simple sample are groups. A user can have many groups,
 * each with its own permissions. The permissions of the groups will
 * then be merged with the holders permissions.
 *
 * Why not just take the GroupInterface? The permission system does
 * not care about groups or any other meaning of just retrieving
 * permissions.
 **/
interface NestedHolderInterface{

    /**
     * Returns subholders of this Holder
     *
     * @return \Traversable<\Permit\Permission\Holder\HolderInterface>
     **/
    public function getSubHolders();

}

