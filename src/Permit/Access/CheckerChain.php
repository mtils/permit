<?php namespace Permit\Access;

use Permit\User\UserInterface;

class CheckerChain implements CheckerInterface
{

    /**
     * @var array
     **/
    protected $checkers;

    /**
     * @var bool
     **/
    protected $returnFallback = false;

    /**
     * @var callable
     **/
    protected $checkerProvider;

    /**
     * @brief Returns if user has access to $resource within $context
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='access')
    {

        foreach ($this->checkers() as $checker) {
            $access = $checker->hasAccess($user, $resource, $context);
            if (is_bool($access)) {
                return $access;
            }
        }

        return $this->returnFallback;

    }

    public function addChecker(CheckerInterface $checker)
    {
        $this->checkers();
        $this->checkers[] = $checker;
        return $this;
    }

    public function removeChecker(CheckerInterface $checker)
    {

        for ($i = 0,$remove = -1; $i < count($this->checkers); $i++) {

            if ($addedChecker == $checker) {
                $remove = $i;
                break;
            }

        }

        if($remove == -1){
            return ;
        }

        unset($this->checkers[$remove]);

        $this->checkers = array_values($this->checkers);
    }

    public function checkers()
    {

        if (is_array($this->checkers)) {
            return $this->checkers;
        }

        $this->checkers = [];

        if ($this->checkerProvider) {
            call_user_func($this->checkerProvider, $this);
        }

        return $this->checkers;
    }

    public function fallbackTo($trueOrFalse)
    {
        $this->returnFallback = $trueOrFalse;
        return $this;
    }

    public function provideCheckers(callable $provider)
    {
        $this->checkerProvider = $provider;
        return $this;
    }

}