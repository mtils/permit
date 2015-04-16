<?php namespace Permit\Doorkeeper;

use DateTime;

class BanState
{

    /**
     * @var \DateTime
     **/
    protected $bannedAt;

    /**
     * @var string
     **/
    protected $reason='';

    /**
     * Return the date the user was banned
     *
     * @return \DateTime|null
     **/
    public function getBannedAt()
    {
        return $this->bannedAt;
    }

    /**
     * Set the date to ban the user
     *
     * @param \DateTime $bannedAt
     * @return self
     **/
    public function setBannedAt(DateTime $bannedAt)
    {
        $this->bannedAt = $bannedAt;
        return $this;
    }

    /**
     * Return a reason for the ban
     *
     * @return string
     **/
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set a reason for this ban
     *
     * @param string $reason
     * @return self
     **/
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Return if the user was banned
     *
     * @return bool
     **/
    public function isBanned()
    {
        return ($this->getBannedAt() instanceof DateTime);
    }

}