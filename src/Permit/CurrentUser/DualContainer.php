<?php namespace Permit\CurrentUser;

use Permit\CurrentUser\LoginValidatorInterface;
use Permit\Holder\HolderInterface;
use InvalidArgumentException;

class DualContainer implements DualContainerInterface{

    /**
     * @brief The container of the actual user
     * @var Permit\CurrentUser\ContainerInterface
     **/
    protected $actualContainer;

    /**
     * @brief The container of the stacked user
     * @var Permit\CurrentUser\ContainerInterface
     **/
    protected $stackedContainer;

    /**
     * @brief The container of a fallback user (like nobody or system)
     * @var Permit\CurrentUser\ContainerInterface
     **/
    protected $fallbackContainer;

    /**
     * @brief The validator which checks a user before (stacked) login
     * @var Permit\CurrentUser\LoginValidatorInterface
     **/
    protected $validator;

    /**
     * @brief Set a returned user forced to self::ACTUAL|self::STACKED
     *
     * @var bool
     **/
    protected $_forceActual = false;

    public function __construct(ContainerInterface $actualContainer,
                                LoginValidatorInterface $validator){

        $this->actualContainer = $actualContainer;
        $this->validator = $validator;

    }

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\Holder\HolderInterface
     **/
    public function user(){

        // The acutal user is forced
        if($this->_forceActual){
            if($user = $this->actualContainer->user()){
                return $user;
            }
        }
        // If not return the stacked
        elseif($user = $this->stackedContainer->user()){
            return $user;
        }

        // If _forceAction is not true and no stacked found
        if($user = $this->actualContainer->user()){
            return $user;
        }

        // Return fallback if you want to
        if($this->fallbackContainer){
            return $this->fallbackContainer->user();
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

        if($actualUser = $this->actualContainer->user()){

            $this->setStackedUser($user, $persist);

            return $user;

        }

        $this->validator->validateOrFail($user);

        $this->actualContainer->setUser($user, $persist);

        return $this;
    }

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser(){
        return $this->reset(self::BOTH);
    }

    /**
     * @brief Returns the user which was acutally logged in, no matter if he
     *        was logged in as some other user
     *
     * @return Permit\Holder\HolderInterface
     **/
    public function actualUser(){
        return $this->actualContainer->user();
    }

    /**
     * @brief Sets the actual user
     *
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Permist the user (in session)
     * @return void
     **/
    public function setActualUser(HolderInterface $user, $persist=true){

        $this->validator->validateOrFail($user);

        $this->actualContainer->setUser($user, $persist);

    }

    /**
     * @brief Return the user currently set by an (admin) to be logged in as.
     *        If the user didnt login as someone different it returns null
     *
     * @return Permit\Holder\HolderInterface|null
     **/
    public function stackedUser(){
        return $this->stackedContainer->user();
    }

    /**
     * @brief Set the stacked user which is the user an admin wants to login
     *        as
     *
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Permist the user (in session)
     * @return void
     **/
    public function setStackedUser(HolderInterface $user, $persist=true){

        if(!$actualUser = $this->actualUser()){
            throw new LoginAsRequiresActualUserException('To login as another user you have do login first');
        }

        $this->validator->validateOrFail($actualUser, $user);

        $this->stackedContainer->setUser($user, $persist);

    }

    /**
     * @brief Force the user returned by user() to be self::ACTUAL
     * 
     * @param bool $force
     **/
    public function forceActual($force=TRUE){
        $this->_forceActual = $force;
    }

    /**
     * @brief Returns if the user returned by user() is the acutal user
     *        which performed the login process
     *
     * @return bool
     **/
    public function isActual(){
        return ( !(bool)$this->stackedUser() && (bool)$this->actualUser());
    }

    /**
     * @brief Resets the container. Resets the current user and actual user
     *
     * @param int $type (optional) Which user to reset
     * @return void
     **/
    public function reset($type=self::BOTH){

        if($type === self::BOTH || $type === self::ACTUAL){
            $this->actualContainer->clearUser();
        }

        if($type === self::BOTH || $type === self::STACKED){
            $this->stackedContainer->clearUser();
        }

    }

    public function getStackedContainer(){
        return $this->stackedContainer;
    }

    public function setStackedContainer(ContainerInterface $container){
        $this->stackedContainer = $container;
    }

    public function getFallbackContainer(){
        return $this->fallbackContainer;
    }

    public function setFallbackContainer(ContainerInterface $container){
        $this->fallbackContainer = $container;
    }

}