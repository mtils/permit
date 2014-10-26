<?php namespace Permit\Support\Sentry\Groups;

trait GroupTrait{

    /**
     * Returns the group's ID.
     *
     * @return mixed
     */
    public function getGroupId(){
        return $this->getId();
    }

}