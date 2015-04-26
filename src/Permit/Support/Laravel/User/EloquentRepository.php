<?php namespace Permit\Support\Laravel\User;


use Illuminate\Database\Eloquent\Model;

use Permit\Registration\UserRepositoryInterface;
use Permit\Registration\ActivatableInterface;
use Permit\User\ProviderInterface;
use Permit\User\UserInterface;


class EloquentRepository implements UserRepositoryInterface
{

    /**
     * The eloquent model (as a prototype)
     **/
    protected $userModel;

    public function __construct(Model $userModel)
    {
        $this->userModel = $userModel;
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
        return $this->userModel->find($identifier);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $attributes
     * @param bool $activate (default: true)
     * @return \Permit\User\UserInterface
     **/
    public function create(array $attributes, $activate=true)
    {

        $user = $this->userModel->newInstance($attributes);

        if ($activate) {
            $user->markAsActivated();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @param Permit\User\UserInterface $user
     **/
    public function save(ActivatableInterface $user)
    {
        return $user->save();
    }

}