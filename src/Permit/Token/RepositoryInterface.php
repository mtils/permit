<?php namespace Permit\Token;


use DateTime;
use Permit\User\UserInterface as User;

/**
 * A Token repository takes care of all tokens inside your application.
 * Activation-Codes, Password-Reminder Tokens, Remember-Tokens, OAuth Tokens
 * and so on will be saved inside a Token-Repository
 **/
interface RepositoryInterface{

    /**
     * A remember token, which authenticates a user by a remember cookie
     *
     * @var int
     **/
    const REMEMBER = 1;

    /**
     * An activation token, used in the Registration process
     *
     * @var int
     **/
    const ACTIVATION = 2;

    /**
     * A password reset token. Send a link with this token to allow users
     * password reset
     *
     * @var int
     **/
    const PASSWORD_RESET = 3;

    /**
     * An oauth token
     *
     * @var int
     **/
    const OAUTH = 4;

    /**
     * A one time login token. This can be used as a password reset if you send
     * the user directly to its password page
     *
     * @var int
     **/
    const LOGIN = 5;

    /**
     * Returns the token of $type for user $user. If the token does not exist it
     * returns an empty string
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @return string $token
     **/
    public function get(User $user, $type);

    /**
     * Returns the authid of token $token. Throws exceptions if no token was found
     * or token is expired
     *
     * @param string $token
     * @param int $type (see self::REMEMBER)
     * @return mixed
     **/
    public function getAuthIdByToken($token, $type);

    /**
     * Creates a $type token for $user.
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param \DateTime $expiresAt Set a custom expiry date
     * @return string The generated token
     **/
    public function create(User $user, $type, DateTime $expiresAt=null);

    /**
     * Updates the token of $type for $user and returns it. Mostly needed for
     * remember tokens
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param \DateTime $expiresAt Set a custom expiry date
     * @return string The generated token
     **/
    public function update(User $user, $type, DateTime $expiresAt=null);

    /**
     * Checks if the $token of $type exists for user $user. An expired token
     * does not exist.
     *
     * @param \Permit\User\UserInterface $user
     * @param string $token
     * @param int $type (see self::REMEMBER...)
     * @return bool
     **/
    public function exists(User $user, $token, $type);

    /**
     * Invalidates token of type $type for user $user. If you have a repository
     * which keeps all tokens, pass it as a third parameter
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param string $token (optional)
     * @return bool
     **/
    public function invalidate(User $user, $type, $token=null);

    /**
     * Purges all expired tokens of type $type
     *
     * @param int $type (see self::REMEMBER...) (optional)
     * @return void
     **/
    public function purgeExpired($type=null);

}