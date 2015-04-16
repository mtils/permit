<?php namespace Permit\Throttle;

use Permit\User\UserInterface;

/**
 * Use this trait in your throttler to allow checks on login
 **/
class ChecksThrottleOnLogin
{

    /**
     * @var Permit\Throttle\ThrottlerInterface
     **/
    protected $throttler;

    public function __construct(ThrottlerInterface $throttler)
    {
        $this->throttler = $throttler;
    }

    /**
     * Hook this into Authenticator::whenLoggedIn
     *
     * @param Permit\User\UserInterface $user
     * @param bool $remember
     * @return void
     **/
    public function recordSucceedLogin(UserInterface $user, $remember)
    {
        $this->throttler->addAttempt($user, true);
    }

    /**
     * Hook this into Authenticator::whenCredentialsInvalid
     *
     * @param Permit\User\UserInterface $user
     * @param array $credentials
     * @param bool $remember
     * @return void
     **/
    public function recordFailedAttempt(UserInterface $user,
                                        array $credentials,
                                        $remember)
    {
        $this->throttler->addAttempt($user, false);
    }

    /**
     * Hook this into Authenticator::whenAttempted
     *
     * @param Permit\User\UserInterface $user
     * @param array $credentials
     * @param bool $remember
     * @return void
     * @throws Permit\Throttle\UserSuspendedException
     **/
    public function check(UserInterface $user, array $credentials, $remember)
    {

        $state = $this->throttler->getSuspensionState($user);
        if ($state->isSuspended()) {
            $exception = new UserSuspendedException('User is suspended');
            $exception->setSuspensionState($state);
            throw $exception;
        }
    }

}