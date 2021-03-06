<?php namespace Permit\Authentication;

use Permit\Registration\ActivatableInterface;

interface CredentialsBrokerInterface
{

    /**
     * Create a token for user with credentials $credentials. Mostly you
     * will send a mail with that token. If you need the user and the token,
     * pass a callable as second parameter. The callable will be called with
     * $user, $token
     *
     * @param array $credentials
     * @param callable $thenDo (optional)
     * @return string The generated $token
     **/
    public function reserveReset(array $credentials, callable $thenDo=null);

    /**
     * Perform the actual password reset. $credentials have to contain
     * the new password (confirmed), and the token generated by reserveReset
     *
     * @param array $credentials
     * @return \Permit\User\UserInterface
     **/
    public function reset(array $credentials);

    /**
     * Update the credentials of user $user. User has to be activatable because
     * CredentialsBroker uses Permit\Registration\UserRepositoryInterface to
     * save the user
     *
     * @param \Permit\Registration\ActivatableInterface $user
     * @param $credentials
     * @return bool
     **/
    public function update(ActivatableInterface $user, array $credentials);

    /**
     * This callable will be called to set the new credentials to your user
     * object. Permit has no idea if you use an orm and if which one.
     * The callable will be called with:
     * (ActivatableInterface $user, array $newCredentials)
     * The password will not be hashed. The UserRepository have to has it
     *
     * @param callable $credentialsSetter
     * @return self
     **/
    public function setCredentialsBy(callable $credentialsSetter);

}