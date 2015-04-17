<?php namespace Permit\Support\Laravel\User;

use RuntimeException;

use Illuminate\Auth\UserProviderInterface as IlluminateProvider;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\GenericUser;

use Permit\User\UserInterface;
use Permit\Authentication\UserProviderInterface;
use Permit\User\ProviderInterface as UserProvider;
use Permit\Registration\UserRepositoryInterface as RegistrationInterface;
use Permit\Registration\Activation\DriverInterface as Activator;
use Permit\Hashing\HasherInterface as Hasher;

class UserProviderRepository implements UserProviderInterface, UserProvider,
                                        RegistrationInterface
{

    public $passwortColumn = 'password';

    /**
     * @var \Illuminate\Auth\UserProviderInterface
     **/
    protected $userProvider;

    /**
     * @var \Permit\Registration\Activation\DriverInterface
     **/
    protected $activator;

    /**
     * @var \Permit\Hashing\HasherInterface
     **/
    protected $hasher;

    public function __construct(IlluminateProvider $provider,
                                Activator $activator,
                                Hasher $hasher)
    {
        $this->userProvider = $provider;
        $this->activator = $activator;
        $this->hasher = $hasher;
    }

    /**
     * {@inheritdoc}
     * 
     * @param array $credentials
     * @return \Permit\User\UserInterface
     **/
    public function findByCredentials(array $credentials)
    {
        return $this->userProvider->retrieveByCredentials($credentials);
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
        return $this->userProvider->retrieveById($identifier);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $attributes
     * @param bool $activate (default: true)
     * @return \Permit\User\UserInterface
     **/
    public function create(array $attributes, $activate=true){

        $user = $this->newUser();

        $passwordLessAttributes = $this->withoutPassword($attributes);

        $user->fill($passwordLessAttributes);

        if($password = $this->onlyPassword($attributes)){
            $user->{$this->passwortColumn} = $this->hasher->hash($password);
        }

        $user->save();

        if ($activate) {
            $this->activator->activate($user);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @param Permit\User\UserInterface $user
     **/
    public function save(UserInterface $user){

        if ($user->isDirty($this->passwortColumn)) {
            $user->{$this->passwortColumn} = $this->hasher->hash($user->{$this->passwortColumn});
        }

        return $user->save();

    }

    public function newUser()
    {
        if (method_exists($this->userProvider, 'createModel')) {
            return $this->userProvider->createModel();
        }
        throw new RuntimeException('UserProvider does not support createModel()');
    }

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

    protected function onlyPassword(array $attributes)
    {
        if (isset($attributes[$this->passwortColumn])) {
            return $attributes[$this->passwortColumn];
        }
    }
}