<?php namespace Permit\Groups;

interface ManagerInterface extends GroupRepositoryInterface{

    /**
     * @brief Returns all groups the user $holder can access
     *
     * @return \Traversable Set of groups
     **/
    public function findAccessableGroupsFor(HolderInterface $holder);

}