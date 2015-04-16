<?php namespace Permit\Doorkeeper;

use Permit\User\UserInterface;

class ChecksBanOnLogin{

    /**
     * @var Permit\Doorkeeper\DoorkeeperInterface
     **/
    protected $doorkeeper;

    public function __construct(DoorkeeperInterface $doorkeeper)
    {
        $this->doorkeeper = $doorkeeper;
    }

    public function check(UserInterface $user)
    {
        $banState = $this->doorkeeper->getBanState($user);

        if ($banState->isBanned($user)){
            $exception = new UserBannedException('User is banned');
            $exception->setBanState($banState);
            throw $exception;
        }
    }

}