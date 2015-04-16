<?php namespace Permit\Doorkeeper;

use Permit\User\UserInterface;

/**
 * A Doorkeeper can ban and unban user. If someome likes to enter your home
 * the doorkeeper will deceide if he is welcome
 */
interface DoorkeeperInterface
{

    /**
     * Ban the user. If the user is banned he can not log into the system
     * anymore. This should be done manually
     *
     * @param \Permit\User\UserInterface $user
     * @param string $reason Give a reason why you have banned him
     * @return bool
     **/
    public function ban(UserInterface $user, $reason='');

    /**
     * Unban the user.
     *
     * @param \Permit\User\UserInterface $user
     * @param string $reason Give a reason why you have banned him
     * @return bool
     **/
    public function unBan(UserInterface $user, $reason='');

    /**
     * Get the ban state for this user. Always return a state.
     *
     * @param \Permit\User\UserInterface $user
     * @return \Permit\Doorkeeper\BanState
     **/
    public function getBanState(UserInterface $user);

}