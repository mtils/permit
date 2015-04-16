<?php namespace Permit\Throttle;

use Permit\User\UserInterface;

/**
 * The throttler tracks failed or succeeded login attempts. It decides
 * completly on his own when to suspend a user. You cant even tell him to
 * suspend or unsuspend users or to clear his login attempts. Suspending users
 * will completly be done by the system. If you need to restrict access to
 * some users, use DoorKeeper::ban()
 *
 **/
interface ThrottlerInterface
{

    /**
     * Adds a attempt for user $user. Returns the new amount of failed attempts
     *
     * @param \Permit\User\UserInterface $user
     * @return int The new amount of failed attempts
     **/
    public function addAttempt(UserInterface $user, $succeeded=true);

    /**
     * Return the suspension state for the user. Allways return a state object
     *
     * @param \Permit\User\UserInterface $user
     * @return \Permit\Throttle\SuspensionState
     **/
    public function getSuspensionState(UserInterface $user);

}