<?php namespace Permit\Support\Laravel\CurrentUser;

use Permit\CurrentUser\ContainerInterface;
use Permit\User\UserInterface;
use Permit\User\ProviderInterface;
use Illuminate\Session\Store;

use InvalidArgumentException;

class SessionContainer implements ContainerInterface{

    protected $_user;

    protected $askedProvider;

    protected $sessionKey = 'permit_user';

    /**
     * @brief The User Provider which loads the actual provider instance
     *
     * @var Permit\User\ProviderInterface
     **/
    protected $provider;

    /**
     * Session store object.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    public function __construct(Store $session, ProviderInterface $provider, $sessionKey='permissioncode_user'){

        $this->session = $session;
        $this->provider = $provider;
        $this->sessionKey = $sessionKey;

    }

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\User\UserInterface
     **/
    public function user(){

        if($this->_user){

            return $this->_user;

        }

        if(!$this->askedProvider){

            if($this->session->has($this->sessionKey)){

                $this->_user = $this->provider->retrieveByAuthId($this->session->get($this->sessionKey));

            }

            $this->askedProvider = true;

            return $this->_user;

        }

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

        if(!$persist){
            $this->_user = $user;
            return $user;
        }

        if($user->isGuest() || $user->isSystem()){
            throw new InvalidArgumentException('You can\'t put guests or system into session');
        }

        $this->session->put($this->sessionKey, $user->getAuthId());

        return $user;

    }

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser(){
        $this->session->forget($this->sessionKey);
        return true;
    }

}