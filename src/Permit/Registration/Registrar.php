<?php namespace Permit\Registration;


use InvalidArgumentException;

use Signal\NamedEvent\BusHolderTrait;

use Permit\User\UserInterface;
use Permit\Token\RepositoryInterface as TokenRepository;
use Permit\Access\AssignerInterface;
use Permit\Registration\ActivatableInterface as ActivatableUser;



/**
 * @brief The registrar registers, activates and deactivates users
 **/
class Registrar implements RegistrarInterface{

    use BusHolderTrait;

    public $registeredEventName  = 'auth.registered';

    public $activatingEventName   = 'auth.activating';

    public $activatedEventName   = 'auth.activated';

    public $activationReservedEventName = 'auth.activation-reserved';

    public $assignedRightsEventName   = 'auth.assigned-rights';

    protected $userRepo;

    protected $tokenRepo;

    protected $accessAssigner;

    protected $eventDispatcher;

    public function __construct(UserRepositoryInterface $userRepo,
                                TokenRepository $tokenRepo,
                                AssignerInterface $accessAssigner){

        $this->userRepo = $userRepo;
        $this->tokenRepo = $tokenRepo;
        $this->accessAssigner = $accessAssigner;

    }

    /**
     * Registers a user. If activation is forced the user will instantly be
     * activated after creation
     *
     * @param  array  $userData
     * @param  bool   $activate (default:false)
     * @return \Permit\Registration\ActivatableInterface
     */
    public function register(array $userData, $activate=false){

        $user = $this->userRepo->create($userData, false);

        $this->fireIfNamed($this->registeredEventName, [$user, $activate]);

        if(!$activate){

            $token = $this->tokenRepo->create($user, TokenRepository::ACTIVATION);

            $this->fireIfNamed($this->activationReservedEventName, [$user, $token]);

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
     * @return \Permit\Registration\ActivatableInterface
     **/
    public function attemptActivation(array $activationData){

        $authId = $this->tokenRepo->getAuthIdByToken(
            $activationData['code'],
            TokenRepository::ACTIVATION
        );

        $user = $this->userRepo->retrieveByAuthId($authId);

        $this->activate($user);

        $this->tokenRepo->invalidate(
            $user,
            TokenRepository::ACTIVATION,
            $activationData['code']
        );

        return $user;
    }

    /**
     * Activates the user.
     *
     * @param \Permit\Registration\ActivatableInterface $user
     * @return \Permit\Registration\ActivatableInterface The activated user with groups assigned (or not)
     **/
    public function activate(ActivatableUser $user){

        // Check for domain exceptions

        $this->checkForActivation($user);

        $this->fireIfNamed($this->activatingEventName, [$user]);

        $user->markAsActivated();

        $user->save();

        if (!$user->isActivated()) {
            throw new ActivationFailedException('Activation has failed');
        }

        $this->fireIfNamed($this->activatedEventName, [$user]);

        $this->accessAssigner->assignAccessRights($user);

        $this->fireIfNamed($this->assignedRightsEventName, [$user]);


        return $user;
    }

    /**
     * Returns if user $user is activated
     *
     * @param \Permit\Registration\ActivatableInterface $user
     * @return bool
     **/
    public function isActivated(ActivatableUser $user){
        return $user->isActivated($user);
    }

    /**
     * Check if an attempt to activate would be senseless/useless
     *
     * @throws \Permit\Registration\UserAlreadyActivatedException If the user is already activated
     * @throws \Permit\Registration\UnactivatebleUserException If this user cant be activated
     * @param \Permit\Registration\ActivatableInterface $user
     * @return void
     **/
    protected function checkForActivation(ActivatableUser $user){

        if($user->isGuest() || $user->isSystem()){
            throw new UnactivatebleUserException('A special user cant by activated');
        }

        if($user->isActivated()){
            throw new UserAlreadyActivatedException('This user is already activated');
        }
    }

    public function getUserRepository(){
        return $this->userRepo;
    }

    public function setUserRepository(UserRepositoryInterface $repo){
        $this->userRepo = $repo;
        return $this;
    }

    public function getAccessAssigner(){
        return $this->accessAssigner;
    }

    public function setAccessAssigner(AssignerInterface $assigner){
        $this->accessAssigner = $assigner;
        return $this;
    }

}