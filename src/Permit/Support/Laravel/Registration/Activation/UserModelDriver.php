<?php namespace Permit\Support\Laravel\Registration\Activation;


use InvalidArgumentException;
use RuntimeException;

use Illuminate\Database\Eloquent\Model;

use Permit\Registration\Activation\DriverInterface;
use Permit\Random\GeneratorInterface;
use Permit\User\UserInterface;
use Permit\Registration\Activation\ActivationDataInvalidException;
use Permit\User\UserNotFoundException;

/**
 * This driver uses a column directly in the user table. I defenetly not
 * recommend this solution but it is compatible with sentry and your used
 * sentry tables.
 * Why not recommend? Activation is a one-timer. Every user will exactly once
 * be activated. Then you carry about this the rest of the users application
 * lifetime. The same is with reset-password codes (which laravel puts in its
 * own table).
 * 
 * By default this driver is configured to work with a default sentry install
 */
class UserModelDriver implements DriverInterface
{

    /**
     * The user will be found by the activation code in this column
     * @var string
     **/
    public $activationCodeColumn = 'activation_code';

    /**
     * If you want to rewrite the activation key length do it here
     * @var string
     **/
    public $activationKeyLength = null;

    /**
     * If have an activation date column in your user model, set it here.
     * If not, set it to ''
     * @var string
     **/
    public $activationDateColumn = 'activated_at';

    /**
     * If have a separate "is activated" column in your user model, set it here.
     * If not, set it to ''
     * @var string
     **/
    public $isActivatedColumn = 'activated';

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $userModel;

    /**
     * @var \Permit\Random\GeneratorInterface
     **/
    protected $codeGenerator;

    /**
     * @param \Illuminate\Database\Eloquent\Model $userModel
     **/
    public function __construct(Model $userModel, GeneratorInterface $generator)
    {

        $this->userModel = $userModel;
        $this->codeGenerator = $generator;

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function reserveActivation(UserInterface $user)
    {

        $this->checkForModelInterface($user);

        if(!$user->exists){
            throw new RuntimeException("The user has to exist to reserve an activation");
        }

        $activationCode = $this->codeGenerator->generate($this->keyLength());

        $user->{$this->activationCodeColumn} = $activationCode;

        return $user->save();

    }

    /**
     * {@inheritdoc}
     *
     * @throws \Permit\Registration\Activation\ActivationDataInvalidException
     * @throws \Permit\User\UserNotFoundException
     * @throws \RuntimeError
     *
     * @param array $activationData The activation params
     * @return \Permit\User\UserInterface
     **/
    public function getUserByActivationData(array $activationData)
    {

        $this->checkActivationData($activationData);

        $code = $activationData['code'];

        $users = $this->userModel
                      ->where($this->activationCodeColumn, $code)
                      ->get();

        if(!count($users)){
            throw new UserNotFoundException("User with code $code not found");
        }

        if(count($users) > 1){
            throw new RuntimeException("Multiple users with code $code found");
        }

        return $users->first();

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function activate(UserInterface $user)
    {

        $this->checkForModelInterface($user);

        if($this->isActivatedColumn){
            $user->{$this->isActivatedColumn} = 1;
        }

        if($this->activationDateColumn){
            $user->{$this->activationDateColumn} = $user->freshTimestamp();
        }

        $user->{$this->activationCodeColumn} = null;

        return $user->save();

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return bool
     **/
    public function isActivated(UserInterface $user)
    {

        $this->checkForModelInterface($user);

        if(!$user->exists){
            return false;
        }

        if($this->isActivatedColumn){
            return (bool)$user->{$this->isActivatedColumn};
        }

        if($this->activationDateColumn){
            return (bool)$user->{$this->activationDateColumn};
        }

        return ($user->{$this->activationCodeColumn} === null);

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @return array
     **/
    public function getActivationData(UserInterface $user)
    {

        $this->checkForModelInterface($user);

        return ['code' => $user->{$this->activationCodeColumn}];

    }

    /**
     * Double checks if the passed user is one of the assigned userModel
     *
     * @param \Permit\User\UserInterface $user
     * @return void
     * @throws \InvalidArgumentException
     **/
    protected function checkForModelInterface($user)
    {

        $userModelClass = get_class($this->userModel);
        if(!$user instanceof $userModelClass){
            throw new InvalidArgumentException("user has to be $userModelClass");
        }
    }

    /**
     * Check if activation data is valid
     *
     * @throws Permit\Registration\Activation\ActivationDataInvalidException
     *
     * @param array $activationData
     * @return void
     **/
    protected function checkActivationData(array $activationData)
    {

        if (!isset($activationData['code']) ||
             strlen($activationData['code']) != $this->keyLength()){
            throw new ActivationDataInvalidException();
        }

    }

    /**
     * Return the desired keylength of activation codes
     *
     * @return int
     **/
    protected function keyLength()
    {
        return $this->activationKeyLength ? $this->activationKeyLength :
            GeneratorInterface::DEFAULT_KEYLENGTH;
    }

}