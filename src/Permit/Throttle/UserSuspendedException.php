<?php namespace Permit\Throttle;

use Permit\Authentication\Exception\LoginException;

class UserSuspendedException extends LoginException{

    /**
     * @var \Permit\Throttle\SuspensionState
     **/
    protected $suspensionState;

    /**
     * @return \Permit\Throttle\SuspensionState
     **/
    public function getSuspensionState()
    {
        return $this->suspensionState;
    }

    /**
     * @param \Permit\Throttle\SuspensionState $state
     * @return self
     **/
    public function setSuspensionState(SuspensionState $state)
    {
        $this->suspensionState = $state;
        return $this;
    }

}