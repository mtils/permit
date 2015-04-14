<?php namespace Permit\Support\Laravel\User;


use Illuminate\Auth\UserProviderInterface as IlluminateProvider;

use Permit\Authentication\UserProviderInterface;

class UserProviderRepository implements UserProviderInterface
{

    /**
     * @var \Illuminate\Auth\UserProviderInterface
     **/
    protected $userProvider;

    public function __construct(IlluminateProvider $provider)
    {
        $this->userProvider = $provider;
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

}