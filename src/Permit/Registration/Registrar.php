<?php namespace Permit\Registration;


use InvalidArgumentException;
use BadMethodCallException;

use Permit\User\UserInterface;
use Permit\Token\RepositoryInterface as TokenRepository;
use Permit\Access\AssignerInterface;
use Permit\Registration\ActivatableInterface as ActivatableUser;
use Ems\Core\Patterns\HookableTrait;

/**
 * @brief The registrar registers, activates and deactivates users
 **/
class Registrar implements RegistrarInterface{

    use HookableTrait;

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
     * {@inheritdoc}
     *
     * @param  array  $userData
     * @param  bool|callable $activation (default:false)
     * @return \Permit\Registration\ActivatableInterface
     */
    public function register(array $userData, $activation=false){

        if (!is_bool($activation) && !is_callable($activation)) {
            throw new BadMethodCallException("Activation has to be either bool or callable");
        }

        $user = $this->userRepo->create($userData, false);

        //$this->fireIfNamed($this->registeredEventName, [$user, $activation]);
        $this->callBeforeListeners('register', [$user, $activation]);

        if ($activation === true) {
            return $this->activate($user);
        }

        $token = $this->tokenRepo->create($user, TokenRepository::ACTIVATION);

        //$this->fireIfNamed($this->activationReservedEventName, [$user, $token]);
        $this->callAfterListeners('register', [$user, $token]);

        if (is_callable($activation)) {
            call_user_func($activation, $user, $token);
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

        //$this->fireIfNamed($this->activatingEventName, [$user]);
        $this->callBeforeListeners('activate', [$user]);

        $user->markAsActivated();

        $user->save();

        if (!$user->isActivated()) {
            throw new ActivationFailedException('Activation has failed');
        }

        //$this->fireIfNamed($this->activatedEventName, [$user]);
        $this->callAfterListeners('activate', [$user]);

        $this->accessAssigner->assignAccessRights($user);

        //$this->fireIfNamed($this->assignedRightsEventName, [$user]);
        $this->callAfterListeners('assignAccessRights', [$user]);


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
