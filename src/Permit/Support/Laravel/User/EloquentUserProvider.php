<?php namespace Permit\Support\Laravel\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Auth\EloquentUserProvider as IlluminateProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

use Signal\NamedEvent\BusHolderTrait;

use Permit\User\UserInterface;
use Permit\Authentication\UserProviderInterface;
use Permit\Registration\UserRepositoryInterface;
use Permit\Registration\ActivatableInterface;
use Permit\Hashing\HasherInterface;
use Permit\Support\Laravel\Hashing\PermitHasher;
use Permit\Token\RepositoryInterface as TokenRepository;
use Permit\Token\TokenException;

/**
 * The EloquentUserProvider implements the UserProvider interface of laravel
 * and Permits UserProvider and Registration\UserRepository. So it can be used
 * for laravels Guard and Permits purposes
 **/
class EloquentUserProvider extends IlluminateProvider implements UserProviderInterface,
                                                                 UserRepositoryInterface
{

    use BusHolderTrait;

    public $passwortColumn = 'password';

    public $creatingUserEvent = 'auth.user.creating';

    public $createdUserEvent = 'auth.user.created';

    public $updatingUserEvent = 'auth.user.updating';

    public $updatedUserEvent = 'auth.user.updating';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $modelInstance;

    /**
     * @var \Permit\Hashing\HasherInterface
     **/
    protected $permitHasher;

    /**
     * @var \Permit\Support\Laravel\Token\RepositoryInterface
     **/
    protected $tokenRepository;

    /**
     * Creates a new provider.
     *
     * @param  \Permit\Hashing\HasherInterface  $permitHasher
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function __construct(Model $modelInstance,
                                HasherInterface $permitHasher,
                                TokenRepository $tokenRepo)
    {
        $this->modelInstance = $modelInstance;
        $this->model = get_class($modelInstance);

        $this->setPermitHasher($permitHasher);

        $this->tokenRepository = $tokenRepo;
    }

    /**
     * {@inheritdoc}
     * 
     * @param array $credentials
     * @return \Permit\User\UserInterface
     **/
    public function findByCredentials(array $credentials)
    {
        return $this->retrieveByCredentials($credentials);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $identifier
     * @param string $counterCheckToken (optional) A token to verify its authenticity
     * @return Permit\User\UserInterface
     **/
    public function retrieveByAuthId($identifier, $counterCheckToken=null)
    {
        return $this->retrieveById($identifier);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $attributes
     * @param bool $activate (default: true)
     * @return \Permit\User\UserInterface
     **/
    public function create(array $attributes, $activate=true){

        $user = $this->createModel();

        $passwordLessAttributes = $this->withoutPassword($attributes);

        $user->fill($passwordLessAttributes);

        if($password = $this->findPassword($attributes)){
            $user->{$this->passwortColumn} = $this->permitHasher->hash($password);
        }

        if ($activate) {
            $user->markAsActivated();
        }

        $this->fireIfNamed($this->creatingUserEvent, [$user, $activate]);

        $user->save();

        $this->fireIfNamed($this->createdUserEvent, [$user, $activate]);

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @param Permit\User\UserInterface $user
     **/
    public function save(ActivatableInterface $user){

        if ($user->isDirty($this->passwortColumn)) {
            $hashedPassword = $this->permitHasher->hash($user->{$this->passwortColumn});
            $user->{$this->passwortColumn} = $hashedPassword;
        }

        $this->fireIfNamed($this->updatingUserEvent, $user);

        $result = $user->save();

        $this->fireIfNamed($this->updatedUserEvent, $user);

        return $result;

    }

    /**
     * Create a new instance of the model. (Overwritten to allow easier mocks)
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        return $this->modelInstance->newInstance();
    }

    /**
     * {@inheritdoc} Reimplemented to allow injecting of separate tokenrepository
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        try {

            $authId = $this->tokenRepository->getAuthIdByToken(
                $token,
                TokenRepository::REMEMBER
            );

            if ($authId == $identifier) {
                return $this->retrieveById($authId);
            }

        } catch (TokenException $e) {}

    }

    /**
     * {@inheritdoc} Reimplemented to allow injecting of separate
     * tokenrepository. Unfortunally the tokenrepository is responsable of
     * generating the tokens so the token parameter will be ignored
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        $this->tokenRepository->update($user, TokenRepository::REMEMBER);
    }

    /**
     * Return the permit hasher (not the laravel proxy)
     *
     * @return \Permit\Hashing\HasherInterface
     **/
    public function getPermitHasher()
    {
        return $this->permitHasher;
    }

    /**
     * Set a new hasher. This is usefull on migrations or jobs for nullHashers
     *
     * @param \Permit\Hashing\HasherInterface $hasher
     * @return self
     **/
    public function setPermitHasher(HasherInterface $hasher)
    {

        $this->permitHasher = $hasher;

        // Proxy the permitHasher for laravel
        $this->hasher = new PermitHasher($hasher);

        return $this;
    }


    /**
     * Unsets the password key from passed attributes
     *
     * @param array $attributes
     * @return array
     **/
    protected function withoutPassword(array $attributes)
    {
        $cleaned = [];

        foreach ($attributes as $key=>$value) {
            if ($key != $this->passwortColumn) {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Returns the password of attributes $attributes if exists
     *
     * @param array $attributes
     * @return string
     **/
    protected function findPassword(array $attributes)
    {
        if (isset($attributes[$this->passwortColumn])) {
            return $attributes[$this->passwortColumn];
        }
    }

}