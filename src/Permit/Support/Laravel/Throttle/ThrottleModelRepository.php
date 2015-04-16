<?php namespace Permit\Support\Laravel\Throttle;


use Illuminate\Database\Eloquent\Model;

use Permit\User\UserInterface;
use Permit\Throttle\ThrottleRepositoryInterface;

class ThrottleModelRepository implements ThrottleRepositoryInterface
{

    public $failedAttemptsColumn = 'attempts';

    public $userIdColumn = 'user_id';

    public $lastFailedDateColumn = 'last_attempt_at';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $throttleModel;

    /**
     * @var array
     **/
    protected $cache;

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     **/
    public function __construct(Model $throttleModel)
    {
        $this->throttleModel = $throttleModel;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return int
     **/
    public function getFailedAttemptCount(UserInterface $user)
    {
        if(!$throttle = $this->getThrottle($user)){
            return 0;
        }

        return $throttle->{$this->failedAttemptsColumn};
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return \DateTime
     **/
    public function getLastFailedAttemptDate(UserInterface $user)
    {
        if(!$throttle = $this->getThrottle($user)){
            return;
        }

        return $throttle->{$this->lastFailedDateColumn};
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return int The new amount of attempts
     **/
    public function addFailedAttempt(UserInterface $user)
    {
        $throttle = $this->getOrCreateThrottle($user);
        $throttle->{$this->failedAttemptsColumn} += 1;
        $throttle->{$this->lastFailedDateColumn} = $throttle->freshTimestamp();
        $throttle->save();
        return $throttle->{$this->failedAttemptsColumn};
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return int The new amount of attempts (0 in this case)
     **/
    public function resetAttempts(UserInterface $user)
    {

        if (!$throttle = $this->getThrottle($user)) {
            return 0;
        }

        $throttle->{$this->failedAttemptsColumn} = 0;
        $throttle->save();

        return 0;
    }

    /**
     * Return a throttle if exists
     *
     * @param \Permit\User\UserInterface $user
     * @return \Illuminate\Database\Eloquent\Model|null
     **/
    public function getThrottle(UserInterface $user)
    {
        $throttle = $this->getOrCreateThrottle($user);
        if ($throttle->exists) {
            return $throttle;
        }
    }

    /**
     * Return a throttle or create (not save)
     *
     * @param \Permit\User\UserInterface $user
     * @return \Illuminate\Database\Eloquent\Model
     **/
    public function getOrCreateThrottle(UserInterface $user)
    {

        if (isset($this->cache[$user->getAuthId()])) {
            return $this->cache[$user->getAuthId()];
        }

        $throttle = $this->throttleModel
                            ->where($this->userIdColumn, $user->getAuthId())
                            ->first();

        $throttle = $throttle ?: $this->newThrottle($user);

        $this->cache[$user->getAuthId()] = $throttle;

        return $this->cache[$user->getAuthId()];

    }

    /**
     * Return a new throttle instance
     *
     * @param \Permit\User\UserInterface $user
     * @return \Illuminate\Database\Eloquent\Model|null
     **/
    public function newThrottle(UserInterface $user)
    {
        $throttle = $this->throttleModel->newInstance();
        $throttle->{$this->userIdColumn} = $user->getAuthId();
        $throttle->{$this->failedAttemptsColumn} = 0;
        return $throttle;
    }

}