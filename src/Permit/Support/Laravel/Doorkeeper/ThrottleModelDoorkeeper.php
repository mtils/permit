<?php namespace Permit\Support\Laravel\Doorkeeper;

use DateTime;

use Permit\User\UserInterface;
use Permit\Doorkeeper\DoorkeeperInterface;
use Permit\Doorkeeper\BanState;
use Permit\Support\Laravel\Throttle\ThrottleModelRepository;

class ThrottleModelDoorkeeper implements DoorkeeperInterface
{

    public $bannedAtColumn = 'banned_at';

    public $reasonColumn = ''; //reason?

    /**
     * @var \Permit\Support\Laravel\Throttle\ThrottleModelRepository
     **/
    protected $throttleRepo;

    /**
     * @param \Permit\Support\Laravel\Throttle\ThrottleModelRepository $throttleRepo
     **/
    public function __construct(ThrottleModelRepository $throttleRepo)
    {
        $this->throttleRepo = $throttleRepo;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param string $reason Give a reason why you have banned him
     * @return bool
     **/
    public function ban(UserInterface $user, $reason='')
    {
        $throttle = $this->throttleRepo->getOrCreateThrottle($user);
        $throttle->{$this->bannedAtColumn} = new DateTime;
        if ($this->reasonColumn) {
            $throttle->{$this->reasonColumn} = $reason;
        }
        $throttle->save();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param string $reason Give a reason why you have banned him
     * @return bool
     **/
    public function unBan(UserInterface $user, $reason='')
    {
        if($throttle = $this->throttleRepo->getThrottle($user)) {
            $throttle->{$this->bannedAtColumn} = null;
            if ($this->reasonColumn) {
                $throttle->{$this->reasonColumn} = $reason;
            }
            $throttle->save();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return \Permit\Doorkeeper\BanState
     **/
    public function getBanState(UserInterface $user)
    {

        $banState = $this->newBanState();

        if (!$throttle = $this->throttleRepo->getThrottle($user)) {
            return $banState;
        }

        if ($throttle->{$this->bannedAtColumn} instanceof DateTime) {
            $banState->setBannedAt($throttle->{$this->bannedAtColumn});
        }

        if ($this->reasonColumn){
            $banState->setReason($throttle->{$this->reasonColumn});
        }

        return $banState;

    }

    protected function newBanState(){
        return new BanState();
    }

}