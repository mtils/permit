<?php namespace Permit\Doorkeeper;

use Permit\Authentication\Exception\LoginException;

class UserBannedException extends LoginException
{
    /**
     * @var \Permit\Doorkeeper\BanState
     **/
    protected $banState;

    /**
     * @return \Permit\Doorkeeper\BanState
     **/
    public function getBanState()
    {
        return $this->banState;
    }

    /**
     * @param \Permit\Doorkeeper\BanState $banState
     * @return self
     **/
    public function setBanState(BanState $banState)
    {
        $this->banState = $banState;
        return $this;
    }

}