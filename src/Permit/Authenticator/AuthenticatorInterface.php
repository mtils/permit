<?php namespace Permit\Authenticator;

interface AuthenticatorInterface{

    /**
     * @brief Authenticates the user. $credentials dont have to be username
     *        and password.
     *
     * @param array $params The (request) params
     * @param string $forceMethod Auf eine bestimmte Authentifizierungsmethode bestehen
     * @return Cartalyst\Sentry\Users\UserInterface;
     **/
    public function authenticate(array $credentials, $remember=true, $tryOthers=false);

}