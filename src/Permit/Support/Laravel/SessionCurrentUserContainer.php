<?php namespace Permit\Support\Laravel;

use Permit\CurrentUser\ContainerInterface;
use Permit\Holder\HolderInterface;
use Permit\Holder\ProviderInterface;
use Illuminate\Session\Store;

use InvalidArgumentException;

class SessionCurrentUserContainer implements ContainerInterface{

    protected $_user;

    protected $askedProvider;

    protected $sessionKey = 'permissioncode_user';

    /**
     * @brief The Holder Provider which loads the actual provider instance
     *
     * @var Permit\Holder\ProviderInterface
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
     * @return Permit\Holder\HolderInterface
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
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Persists the user (in session)
     * @return Permit\Holder\HolderInterface
     **/
    public function setUser(HolderInterface $user, $persist=true){

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