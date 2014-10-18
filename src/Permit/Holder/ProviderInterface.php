<?php namespace Permit\Holder;

interface ProviderInterface{

    /**
     * @brief Find a user by its auth id (which is stored in session)
     *
     * @param mixed $identifier
     * @param string $counterCheckToken (optional) A token to verify its authenticity
     * @return Permit\Holder\HolderInterface
     **/
    public function retrieveByAuthId($identifier, $counterCheckToken=null);

}