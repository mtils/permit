<?php namespace Permit\CurrentUser;

use Permit\User\UserInterface;
use Permit\User\GenericUser;

use BadMethodCallException;

class SystemDetector{
    public function isSystem(){
        return (php_sapi_name() == 'cli');
    }
}

class FallbackContainer implements ContainerInterface{

    protected $_guestUser;

    protected $_systemUser;

    protected $systemDetector;

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\User\UserInterface
     **/
    public function user(){

        if($this->isConsole()){
            return $this->getSystem();
        }
        return $this->getGuest();
    }

    protected function isConsole(){
        return $this->getSystemDetector()->isSystem();
    }

    protected function getSystemDetector(){
        if(!$this->systemDetector){
            $this->systemDetector = new SystemDetector();
        }
        return $this->systemDetector;
    }

    protected function setSystemDetector(SystemDetector $detector){
        $this->systemDetector = $detector;
    }

    public function getGuest(){

        if(!$this->_guestUser){
            $this->_guestUser = new GenericUser();
            $this->_guestUser->setAuthId('guest');
            $this->_guestUser->setIsGuest(true);
        }

        return $this->_guestUser;

    }

    public function getSystem(){

        if(!$this->_systemUser){
            $this->_systemUser = new GenericUser();
            $this->_systemUser->setAuthId('system');
            $this->_systemUser->setIsSystem(true);
        }

        return $this->_systemUser;

    }

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you shoul simply set a user a second time
     *
     * @param Permit\User\UserInterface $user
     * @param bool $persist Persists the user (in session)
     * @return Permit\User\UserInterface
     **/
    public function setUser(UserInterface $user, $persist=true){
        throw new BadMethodCallException('You cannot write into an readonly container');
    }

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser(){
        throw new BadMethodCallException('You cannot write into an readonly container');
    }
}