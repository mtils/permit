<?php namespace Permit\Throttle;

use Permit\User\UserInterface;

interface ThrottleRepositoryInterface{

    /**
     * Returns the amount of failed attempts (until the last reset)
     *
     * @param \Permit\User\UserInterface $user
     * @return int
     **/
    public function getFailedAttemptCount(UserInterface $user);

    /**
     * Returns the last failed attempt date
     *
     * @param \Permit\User\UserInterface $user
     * @return \DateTime
     **/
    public function getLastFailedAttemptDate(UserInterface $user);

    /**
     * Adds a failed attempt for user $user
     *
     * @param \Permit\User\UserInterface $user
     * @return int The new amount of attempts
     **/
    public function addFailedAttempt(UserInterface $user);

    /**
     * Resets the attempts. If you have one table row per user, just reset its
     * values. If you have something like a security log, add a succeded
     * attempt with the newest date or so
     *
     * @param \Permit\User\UserInterface $user
     * @return int The new amount of attempts (0 in this case)
     **/
    public function resetAttempts(UserInterface $user);

}
