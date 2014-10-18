<?php namespace Permit\Support\Sentry;

use Permit\Holder\ProviderInterface;
use Cartalyst\Sentry\Users\ProviderInterface AS SentryProviderInterface;

class HolderProvider implements ProviderInterface{

    protected $sentryProvider;

    public function __construct(SentryProviderInterface $provider){

        $this->sentryProvider = $provider;

    }

    /**
     * @brief Find a user by its auth id (which is stored in session)
     *
     * @param mixed $identifier
     * @param string $counterCheckToken (optional) A token to verify its authenticity
     * @return Permit\Holder\HolderInterface
     **/
    public function retrieveByAuthId($identifier, $counterCheckToken=null){
        return $this->sentryProvider->findById($identifier);
    }

}