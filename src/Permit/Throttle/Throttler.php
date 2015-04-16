<?php namespace Permit\Throttle;


use DateTime;
use DateInterval;

use Permit\User\UserInterface;

class Throttler implements ThrottlerInterface
{

    /**
     * @var \Permit\Throttle\Throttler
     **/
    protected $repository;

    public $suspendAfter = 5;

    public $suspendMinutes = 15;

    public function __construct(ThrottleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return int The new amount of failed attempts
     **/
    public function addAttempt(UserInterface $user, $succeeded=true)
    {

        if ($succeeded) {
            return $this->repository->resetAttempts($user);
        }

        return $this->repository->addFailedAttempt($user);

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return \Permit\Throttle\SuspensionState
     **/
    public function getSuspensionState(UserInterface $user)
    {

        $failedCount = $this->repository->getFailedAttemptCount($user);

        $suspension = $this->newSuspensionState();
        $suspension->setFailedAttemptCount($failedCount);

        $lastFailedDate = $this->repository->getLastFailedAttemptDate($user);

        if (!$lastFailedDate) {
            return $suspension;
        }

        $suspension->setLastFailedAttemptDate($lastFailedDate);

        if ($suspension->getFailedAttemptCount() <= $this->suspendAfter ) {
            return $suspension;
        }

        $untilDate = $this->getSuspendUntilDate($lastFailedDate);
        $suspension->setValidUntil($untilDate);

        return $suspension;

    }

    /**
     * Get the date when the suspension ends
     *
     * @param \DateTime $lastFailedDate
     * @return \DateTime
     **/
    protected function getSuspendUntilDate(DateTime $lastFailedDate)
    {
        $untilDate = clone $lastFailedDate;
        $untilDate->modify("+{$this->suspendMinutes} minutes");
        return $untilDate;
    }

    /**
     * Instanciate a SuspensionState object
     *
     * @return \Permit\Throttle\SuspensionState
     **/
    protected function newSuspensionState()
    {
        return new SuspensionState;
    }

}