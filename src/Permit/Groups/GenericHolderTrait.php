<?php namespace Permit\Groups;

trait GenericHolderTrait{

    protected $groups = array();

    /**
     * @brief Returns all groups the holder is attached to
     *
     * @return \Traversable
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @brief Attaches group $group to this holder
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function attachGroup(GroupInterface $group){
        $this->groups[] = $group;
        return true;
    }

    /**
     * @brief Detaches group $group from holder
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function detachGroup(GroupInterface $group){
        $this->groups = array_filter($this->groups, function($attached) use ($group){
            return ($attached->getGroupId() != $group->getGroupId());
        });
        return true;
    }

    /**
     * Returns if this holder is attached to group $group
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function isInGroup(GroupInterface $group){
        foreach($this->groups as $attached){
            if($attached->getGroupId() == $group->getGroupId()){
                return true;
            }
        }
        return false;
    }

}