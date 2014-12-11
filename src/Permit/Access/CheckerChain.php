<?php namespace Permit\Access;

use Permit\User\UserInterface;

class CheckerChain implements CheckerInterface{

    protected $checkers = [];

    protected $returnFallback = true;

    /**
     * @brief Returns if user has access to $resource within $context
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='access'){

        foreach($this->checkers as $checker){
            if(!$checker->hasAccess($user, $resource, $context)){
                return false;
            }
        }

        return $this->returnFallback;

    }

    public function addChecker(CheckerInterface $checker){
        $this->checkers[] = $checker;
        return $this;
    }

    public function removeChecker(CheckerInterface $checker){

        for($i = 0,$remove = -1; $i < count($this->checkers); $i++){

            if($addedChecker == $checker){
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

    public function fallbackTo($trueOrFalse){
        $this->returnFallback = $trueOrFalse;
        return $this;
    }

}