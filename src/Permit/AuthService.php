<?php namespace Permit;

use BadMethodCallException;
use InvalidArgumentException;

use Permit\CurrentUser\DualContainerInterface;
use Permit\Checker\CheckerInterface;
use Permit\Holder\HolderInterface;


class AuthService implements CheckerInterface, DualContainerInterface{

    /**
     * @brief The current user container
     * @var Permit\CurrentUser\ContainerInterface
     **/
    protected $container;

    /**
     * @brief The permission checker
     * @var Permit\Checker\CheckerInterface
     **/
    protected $permissionChecker;

    /**
     * @brief Users are temporarly stored here between can() and access()
     * @var Permit\Holder\HolderInterface
     **/
    protected $canStore;

    /**
     * @brief The fallback object to call any method on this object and redirect
     *        to your source object
     * @var object
     **/
    protected $fallbackObject;

    public function __construct(DualContainerInterface $container,
                                CheckerInterface $permissionChecker){

        $this->container = $container;
        $this->permissionChecker = $permissionChecker;

    }

    /**
     * @brief Retrieve the current user.
     *
     * @return Permit\Holder\HolderInterface
     **/
    public function user(){
        return $this->container->user();
    }

    /**
     * @brief Set the current user. If a user should be logged in as a
     *        different user you shoul simply set a user a second time
     *
     * @param Permit\Holder\HolderInterface $user
     **/
    public function setUser(HolderInterface $user, $persist=true){

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
     * @return Permit\Holder\HolderInterface
     **/
    public function actualUser(){
        return $this->container->actualUser();
    }

    /**
     * @brief Sets the actual user
     *
     * @param Permit\Holder\HolderInterface $user
     * @param bool $persist Permist the user (in session)
     * @return void
     **/
    public function setActualUser(HolderInterface $user, $persist=true){
        $this->container->setActualUser($user, $persist);
    }

    /**
     * @brief Return the user currently set by an (admin) to be logged in as.
     *        If the user didnt login as someone different it returns null
     *
     * @return Permit\Holder\HolderInterface|null
     **/
    public function stackedUser(){
        return $this->container->getStackedUser();
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
     * @brief Returns if holder has acces to $resourceOrCode within $context
     *
     * @param Permit\Holder\HolderInterface $holder The Holder of permission codes
     * @param string|Permit\PermissionableInterface The resource
     * @param int $context (optional)
     * @return bool
     **/
    public function hasAccess(HolderInterface $holder, $resourceOrCode, $context=PermissionableInterface::ACCESS){
        return $this->permissionChecker->hasAccess($holder, $resourceOrCode, $context);
    }

    /**
     * @brief Helper function to check if the current user has Access to
     *        $resourceOrCode
     *
     * @param string|Permit\PermissionableInterface The resource
     * @param int $context (optional)
     * @return bool
     **/
    public function allowed($resourceOrCode, $context=PermissionableInterface::ACCESS){
        return $this->hasAccess($this->user(), $resourceOrCode, $context);
    }

    /**
     * @brief Helper method for readable fluid syntax:
     *        if( Auth::can($user)->access($resource) )
     *
     * @param Permit\Holder\HolderInterface $holder
     * @return self
     **/
    public function can(HolderInterface $holder){
        $this->canStore = $holder;
        return $this;
    }

    /**
     * @brief Second helper method fpr fluid syntax
     *        if( Auth::can($user)->access($resource) )
     *
     * @param string|Permit\PermissionableInterface The resource
     * @param int $context (optional)
     * @return bool
     **/
    public function access($resourceOrCode, $context=PermissionableInterface::ACCESS){

        if(!$this->canStore instanceof HolderInterface){
            throw new BadMethodCallException('Call can($user) before access($resource)');
        }

        $result = $this->hasAccess($this->canStore, $resourceOrCode, $context);
        $this->canStore = NULL;
        return $result;
    }

    /**
     * @brief Set a fallback Object to have all wellknown methods available
     *
     * @param object $fallback
     * @return void
     **/
    public function setFallback($fallback){

        if(!is_object($fallback)){
            throw new InvalidArgumentException('$fallback has to be an object not ' . gettype($fallback));
        }

        $this->fallback = $fallback;

    }

    /**
     * @brief Call any method on fallback object
     * @see self::setFallback()
     **/
    public function __call($method, array $params){

        if(method_exists($this->fallback, $method)){
            return call_user_func_array([$this->fallback, $method], $params);
        }
        if(method_exists($this->fallback, '__call')){
            return $this->fallback->__call($method, $params);
        }

        throw new BadMethodCallException("Method $method does not exists");

    }

    public function loggedIn(){
        return !($this->user()->isGuest());
    }

}