<?php namespace Permit\Groups;

interface HolderInterface{

    /**
     * @brief Returns all groups the holder is attached to
     *
     * @return \Traversable
     */
    public function getGroups();

    /**
     * @brief Attaches group $group to this holder
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function attachGroup(GroupInterface $group);

    /**
     * @brief Detaches group $group from holder
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function detachGroup(GroupInterface $group);

    /**
     * Returns if this holder is attached to group $group
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function isInGroup(GroupInterface $group);

}