<?php namespace Permit\User;

use OutOfBoundsException;

class UserNotFoundException extends OutOfBoundsException{};

interface ProviderInterface{

    /**
     * @brief Find a user by its auth id (which is stored in session)
     *
     * @param mixed $identifier
     * @param string $counterCheckToken (optional) A token to verify its authenticity
     * @return Permit\User\UserInterface
     **/
    public function retrieveByAuthId($identifier, $counterCheckToken=null);

}