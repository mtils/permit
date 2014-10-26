<?php namespace Permit\Support\Sentry\Groups;

use Permit\Groups\GroupInterface;

trait HolderTrait{

    /**
     * @brief Attaches group $group to this holder
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function attachGroup(GroupInterface $group){
        return $this->addGroup($group);
    }

    /**
     * @brief Detaches group $group from holder
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function detachGroup(GroupInterface $group){
        return $this->removeGroup($group);
    }

    /**
     * Returns if this holder is attached to group $group
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function isInGroup(GroupInterface $group){
        return $this->inGroup($group);
    }

}