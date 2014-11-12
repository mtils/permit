<?php namespace Permit\Support\Sentry\Registration;

use Permit\Registration\UserRepositoryInterface;
use Permit\User\UserInterface;

use Cartalyst\Sentry\Users\ProviderInterface;

class UserRepository implements UserRepositoryInterface{

    protected $sentryProvider;

    public function __construct(ProviderInterface $sentryProvider){

        $this->sentryProvider = $sentryProvider;

    }

    /**
     * @brief Creates a user with attributes $attributes
     *
     * @param array $attributes
     * @return \Permit\User\UserInterface
     **/
    public function create(array $attributes){
        return $this->sentryProvider->create($attributes);
    }

    /**
     * @brief Saves the user
     *
     * @param Permit\User\UserInterface $user
     **/
    public function save(UserInterface $user){
        $user->save();
    }

}