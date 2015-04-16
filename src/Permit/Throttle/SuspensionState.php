<?php namespace Permit\Throttle;


use DateTime;

class SuspensionState
{

    /**
     * @var \DateTime
     **/
    protected $validUntil;

    /**
     * @var int
     **/
    protected $failedAttemptCount;

    /**
     * @var \DateTime
     **/
    protected $lastFailedAttemptDate;

    /**
     * Returns the date and time when the suspension ends (the use can login)
     *
     * @return \DateTime
     **/
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set the date when this suspension will end
     *
     * @param \DateTime $validUntil
     * @return self
     **/
    public function setValidUntil(DateTime $validUntil)
    {
        $this->validUntil = $validUntil;
        return $this;
    }

    /**
     * Return how many failed login attempts has caused this suspension
     *
     * @return int
     **/
    public function getFailedAttemptCount()
    {
        return $this->failedAttemptCount;
    }

    /**
     * Set the number of failed attempts which caused the suspension
     *
     * @param int $failedAttempts
     * @return self
     **/
    public function setFailedAttemptCount($failedAttempts)
    {
        $this->failedAttemptCount = $failedAttempts;
        return $this;
    }

    /**
     * Returns the date of the last failed login
     *
     * @return \DateTime
     **/
    public function getLastFailedAttemptDate()
    {
        return $this->lastFailedAttemptDate;
    }

    /**
     * Set the last datetime the user attempted wrong credentials
     *
     * @param \DateTime $lastFailedDate
     * @return self
     **/
    public function setLastFailedAttemptDate(DateTime $lastFailedDate)
    {
        $this->lastFailedAttemptDate = $lastFailedDate;
        return $this;
    }

    /**
     * Returns if the user is suspended
     *
     * @return bool
     **/
    public function isSuspended(DateTime $atDate=null)
    {
        if (!$validUntil = $this->getValidUntil()) {
            return false;
        }

        $atDate = $atDate ?: new DateTime;

        return $atDate <= $validUntil;
    }

}