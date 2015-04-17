<?php namespace Permit;

use BadMethodCallException;
use InvalidArgumentException;

use Permit\CurrentUser\DualContainerInterface;
use Permit\Access\CheckerInterface;
use Permit\Permission\Holder\HolderInterface;
use Permit\User\UserInterface;
use Permit\Permission\PermissionableInterface;
use Permit\Registration\RegistrarInterface;

class AuthService implements CheckerInterface, DualContainerInterface{

    /**
     * @brief The current user container
     * @var Permit\CurrentUser\ContainerInterface
     **/
    protected $container;

    /**
     * @brief The permission checker
     * @var Permit\Access\CheckerInterface
     **/
    protected $permissionChecker;

    /**
     * @brief The object registering users
     *
     * @var Permit\Registration\RegistrarInterface
     **/
    protected $registrar;


    /**
     * @brief Users are temporarly stored here between can() and access()
     * @var Permit\User\UserInterface
     **/
    protected $canStore;

    /**
     * @brief You can add fallback Objects which Auth will use if a method does
     *        not exist
     *
     * @see self::addFallBack()
     * @var array
     **/
    protected $fallbackObjects = [];

    public function __construct(DualContainerInterface $container,
                                CheckerInterface $permissionChecker){

        $this->container = $container;
        $this->permissionChecker = $permissionChecker;

    }

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\User\UserInterface
     **/
    public function user(){
        return $this->container->user();
    }

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you shoul simply set a user a second time
     *
     * @param Permit\User\UserInterface $user
     **/
    public function setUser(UserInterface $user, $persist=true){

        $this->container->setUser($user, $persist);

        return $user;

    }

    /**
     * @brief Sets the user to null
     *
     * @return bool
     **/
    public function clearUser(){
        return $this->container->clearUser();
    }

    /**
     * @brief Returns the user which was acutally logged in, no matter if he
     *        was logged in as some other user
     *
     * @return Permit\User\UserInterface
     **/
    public function actualUser(){
        return $this->container->actualUser();
    }

    /**
     * @brief Sets the actual user
     *
     * @param Permit\User\UserInterface $user
     * @param bool $persist Permist the user (in session)
     * @return void
     **/
    public function setActualUser(UserInterface $user, $persist=true){
        $this->container->setActualUser($user, $persist);
    }

    /**
     * @brief Return the user currently set by an (admin) to be logged in as.
     *        If the user didnt login as someone different it returns null
     *
     * @return Permit\User\UserInterface|null
     **/
    public function stackedUser(){
        return $this->container->getStackedUser();
    }

    /**
     * @brief Set the stacked user which is the user an admin wants to login
     *        as
     *
     * @param Permit\User\UserInterface $user
     * @param bool $persist Permist the user (in session)
     * @return void
     **/
    public function setStackedUser(UserInterface $user, $persist=true){
        return $this->container->setStackedUser($user, $persist);
    }

    /**
     * @brief Force the user returned by user() to be self::ACTUAL
     * 
     * @param bool $force
     **/
    public function forceActual($force=TRUE){
        return $this->container->forceActual($force);
    }

    /**
     * @brief Returns if the user returned by user() is not the acutally user
     *        which performed the login process
     *
     * @return bool
     **/
    public function isActual(){
        return $this->container->isActual();
    }

    /**
     * @brief Resets the container. Resets the current user and actual user
     *
     * @param int $type (optional) Which user to reset
     * @return void
     **/
    public function reset($type=DualContainerInterface::BOTH){
        return $this->container->reset($type);
    }

    /**
     * @brief Returns if user has access to $resource within $context
     *
     * @param Permit\User\UserInterface $user The Holder of permission codes
     * @param mixed $resource The resource
     * @param int $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='access'){
        return $this->permissionChecker->hasAccess($user, $resource, $context);
    }

    /**
     * @brief Helper function to check if the current user has Access to
     *        $resourceOrCode
     *
     * @param mixed $resource resource
     * @param int $context (optional)
     * @return bool
     **/
    public function allowed($resource, $context='access'){
        return $this->hasAccess($this->user(), $resource, $context);
    }

    /**
     * @brief Helper method for readable fluid syntax:
     *        if( Auth::can($user)->access($resource) )
     *
     * @param Permit\User\UserInterface $holder
     * @return self
     **/
    public function can(UserInterface $holder){
        $this->canStore = $holder;
        return $this;
    }

    /**
     * @brief Second helper method fpr fluid syntax
     *        if( Auth::can($user)->access($resource) )
     *
     * @param mixed $resource The resource
     * @param int $context (optional)
     * @return bool
     **/
    public function access($resource, $context='access'){

        if(!$this->canStore instanceof UserInterface){
            throw new BadMethodCallException('Call can($user) before access($resource)');
        }

        $result = $this->hasAccess($this->canStore, $resource, $context);
        $this->canStore = NULL;
        return $result;
    }

    public function getRegistrar(){
        return $this->registrar;
    }

    public function setRegistrar(RegistrarInterface $registrar){
        $this->registrar = $registrar;
        return $this;
    }

    /**
     * @brief Set a fallback Object to have all wellknown methods available
     *
     * @param object $fallback
     * @return void
     **/
    public function addFallback($fallback){

        if(!is_object($fallback)){
            throw new InvalidArgumentException('$fallback has to be an object not ' . gettype($fallback));
        }

        $this->fallbackObjects[] = $fallback;

    }

    /**
     * @brief Call any method on fallback object
     * @see self::setFallback()
     **/
    public function __call($method, array $params){

        // First try on registrar
        if($this->registrar){
            if(method_exists($this->registrar, $method)){
                return call_user_func_array([$this->registrar, $method], $params);
            }
        }

        // Then direct methods on fallbackObjects
        foreach($this->fallbackObjects as $fallback){

            if(method_exists($fallback, $method)){
                return call_user_func_array([$fallback, $method], $params);
            }

        }

        // Then look for overloaded methods in fallbacks
        foreach($this->fallbackObjects as $fallback){

            if(method_exists($fallback, __call)){
                return $fallback->__call($method, $params);
            }

        }

        throw new BadMethodCallException("Method $method does not exists");

    }

    public function loggedIn(){
        return !($this->user()->isGuest());
    }

}