<?php namespace Permit\Support\Laravel\Authentication;

use Permit\User\ProviderInterface;
use Illuminate\Contracts\Auth\UserProvider;

class MiniUserProvider implements ProviderInterface
{

    protected $illuminateProvider;

    public function __construct(UserProvider $illuminateProvider)
    {
        $this->illuminateProvider = $illuminateProvider;
    }

    /**
     * @brief Find a user by its auth id (which is stored in session)
     *
     * @param mixed $identifier
     * @param string $counterCheckToken (optional) A token to verify its authenticity
     * @return Permit\User\UserInterface
     **/
    public function retrieveByAuthId($identifier, $counterCheckToken=null)
    {
        return $this->illuminateProvider->retrieveById($identifier);
    }

}