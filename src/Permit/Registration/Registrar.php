<?php namespace Permit\Registration;

use Permit\Registration\Activation\DriverInterface;
use Permit\Access\AssignerInterface;
use InvalidArgumentException;

/**
 * @brief The registrar registers, activates and deactivates users
 **/
class Registrar implements RegistrarInterface{

    public $registeredEventName  = 'auth.registered';

    public $activatedEventName   = 'auth.activated';

    public $assignedRightsEventName   = 'auth.assigned-rights';

    public $deactivatedEventName = 'auth.deactivated';

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
     * @brief Registers a user. If activation is forced the user will instantly be
     *        activated after creation
     *
     * @param  array  $userData
     * @param  bool   $activate (default:false)
     * @return \Permit\Registration\ActivatableUserInterface;
     */
    public function register(array $userData, $activate=false){

        $user = $this->userRepo->create($userData);

        $this->fire($this->registeredEventName, [$user, $activate]);

        if(!$activate){
            $this->activationDriver->reserveActivation($userData);
        }
        else{
            $this->activate($user, [], true);
        }

        return $user;
    }

    /**
     * @brief Activates the user
     *
     * @param \Permit\Registration\ActivatableUserInterface $user
     * @param array $params The parameters like an activation code
     * @param bool $force Force the activation and skip normal activation validation
     * @return \Permit\Registration\ActivatableUserInterface The activated user with groups assigned (or not)
     **/
    public function activate(ActivatableUserInterface $user, array $params=[], $force=false){

        if($force){
            $user->activate();
        }
        else{
            $this->activationDriver->attemptActivation($user, $params);
        }

        $this->fire($this->activatedEventName, [$user, $force]);

        $this->accessAssigner->assignAccessRights($user);

        $this->fire($this->assignedRightsEventName, [$user, $force]);

        return $user;
    }

    /**
     * @brief Deactivates a user
     *
     * @param \Permit\Registration\ActivatableUserInterface $user
     * @return bool
     **/
    public function deactivate(ActivatableUserInterface $user){
        $user->deactivate();
        return TRUE;
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

    public function getEventDispatcher(){
        return $this->eventDispatcher;
    }

    public function setEventDispatcher($dispatcher){

        if(!is_object($dispatcher) || method_exists($dispatcher,'fire')){
            throw new InvalidArgumentException('EventDispatcher has to have a fire() method');
        }

        $this->eventDispatcher = $dispatcher;

        return $this;

    }

}