<?php namespace Permit\Registration;

use Permit\User\UserInterface;
use Permit\Registration\Activation\DriverInterface;
use Permit\Access\AssignerInterface;
use InvalidArgumentException;

/**
 * @brief The registrar registers, activates and deactivates users
 **/
class Registrar implements RegistrarInterface{

    public $registeredEventName  = 'auth.registered';

    public $activatedEventName   = 'auth.activated';

    public $activationReservedEventName = 'auth.activation-reserved';

    public $assignedRightsEventName   = 'auth.assigned-rights';

    protected $userRepo;

    protected $activationDriver;

    protected $accessAssigner;

    protected $eventDispatcher;

    public function __construct(UserRepositoryInterface $userRepo,
                                DriverInterface $activationDriver,
                                AssignerInterface $accessAssigner){

        $this->userRepo = $userRepo;
        $this->activationDriver = $activationDriver;
        $this->accessAssigner = $accessAssigner;

    }

    /**
     * Registers a user. If activation is forced the user will instantly be
     * activated after creation
     *
     * @param  array  $userData
     * @param  bool   $activate (default:false)
     * @return \Permit\User\UserInterface
     */
    public function register(array $userData, $activate=false){

        $user = $this->userRepo->create($userData, false);

        $this->fire($this->registeredEventName, [$user, $activate]);

        if(!$activate){

            $this->activationDriver->reserveActivation($user);

            $this->fire($this->activationReservedEventName, [$user]);

        }
        else{
            $this->activate($user);
        }

        return $user;
    }

    /**
     * Try to activate an user by activationParams like a code or many
     *
     * @param array $activationData
     * @return \Permit\User\UserInterface
     **/
    public function attemptActivation(array $activationData){

        $user = $this->activationDriver->getUserByActivationData($activationData);

        $this->activate($user);

        return $user;
    }

    /**
     * Activates the user.
     *
     * @param \Permit\User\UserInterface $user
     * @return \Permit\User\UserInterface The activated user with groups assigned (or not)
     **/
    public function activate(UserInterface $user){

        // Check for domain exceptions

        $this->checkForActivation($user);

        $this->activationDriver->activate($user);

        if(!$this->activationDriver->isActivated($user)){

            throw new ActivationFailedException('Activation has failed');

        }

        $this->fire($this->activatedEventName, [$user]);

        $this->accessAssigner->assignAccessRights($user);

        $this->fire($this->assignedRightsEventName, [$user]);


        return $user;
    }

    /**
     * Returns if user $user is activated
     *
     * @param Permit\User\UserInterface $user
     * @return bool
     **/
    public function isActivated(UserInterface $user){
        return $this->activationDriver->isActivated($user);
    }

    /**
     * Check if an attempt to activate would be senseless/useless
     *
     * @throws \Permit\Registration\UserAlreadyActivatedException If the user is already activated
     * @throws \Permit\Registration\UnactivatebleUserException If this user cant be activated
     * @param \Permit\User\UserInterface
     * @return void
     **/
    protected function checkForActivation(UserInterface $user){

        if($user->isGuest() || $user->isSystem()){

            throw new UnactivatebleUserException('A special user cant by activated');

        }

        if($this->activationDriver->isActivated($user)){

            throw new UserAlreadyActivatedException('This user is already activated');

        }
    }

    /**
     * @brief Fires an event in different steps of the registration/activation
     *        process
     *
     * @param string $eventName The name of the event
     * @param array $params The parameters
     * @return void
     **/
    protected function fire($eventName, array $params){

        if($this->eventDispatcher){
            $this->eventDispatcher->fire($eventName, $params);
        }

    }

    public function getUserRepository(){
        return $this->userRepo;
    }

    public function setUserRepository(UserRepositoryInterface $repo){
        $this->userRepo = $repo;
        return $this;
    }

    public function getActivationDriver(){
        return $this->activationDriver;
    }

    public function setActivationDriver(DriverInterface $driver){
        $this->activationDriver = $driver;
        return $this;
    }

    public function getAccessAssigner(){
        return $this->accessAssigner;
    }

    public function setAccessAssigner(AssignerInterface $assigner){
        $this->accessAssigner = $assigner;
        return $this;
    }

    public function getEventDispatcher(){
        return $this->eventDispatcher;
    }

    public function setEventDispatcher($dispatcher){

        if(!is_object($dispatcher) || !method_exists($dispatcher,'fire')){
            throw new InvalidArgumentException('EventDispatcher has to have a fire() method');
        }

        $this->eventDispatcher = $dispatcher;

        return $this;

    }

}