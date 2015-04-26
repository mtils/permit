<?php namespace Permit\Support\Sentry\Token;

use DateTime;
use InvalidArgumentException;
use RuntimeException;
use Exception;

use Permit\Token\RepositoryInterface;
use Permit\User\UserInterface as User;
use Permit\Token\TokenExpiredException;
use Permit\Token\TokenInvalidException;
use Permit\Token\TokenNotFoundException;

use Cartalyst\Sentry\Users\UserInterface as SentryUserInterface;
use Cartalyst\Sentry\Users\ProviderInterface as SentryProvider;
use Cartalyst\Sentry\Users\UserNotFoundException;

class SentryTokenRepository implements RepositoryInterface
{

    /**
     * @var Cartalyst\Sentry\Users\ProviderInterface
     **/
    protected $userProvider;

    public function __construct(SentryProvider $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @return string $token
     **/
    public function get(User $user, $type)
    {

        $this->checkForSentryInterface($user);

        if ($type == self::REMEMBER) {
            return $user->getPersistCode();
        }

        if ($type == self::ACTIVATION) {
            return $user->getActivationCode();
        }

        if ($type == self::PASSWORD_RESET) {
            return $user->getResetPasswordCode();
        }

        throw new InvalidArgumentException("Unsupported token type $type");

    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     * @param int $type (see self::REMEMBER)
     * @return mixed
     **/
    public function getAuthIdByToken($token, $type)
    {

        if (!$this->isValid($token)) {
            throw new TokenInvalidException("Token $token is invalid");
        }

        if ($type == self::REMEMBER) {
            return $this->getByRememberToken($token)->getId();
        }

        if ($type == self::ACTIVATION) {
            return $this->getByActivationCode($token)->getId();
        }

        if ($type == self::PASSWORD_RESET) {
            return $this->getByResetPasswordCode($token)->getId();
        }

        throw new InvalidArgumentException("Unsupported token type $type");

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param \DateTime $expiresAt Set a custom expiry date
     * @return string The generated token
     **/
    public function create(User $user, $type, DateTime $expiresAt=null)
    {

        $this->checkForSentryInterface($user);

        // In every case Sentry saves the user (on getX!)

        if ($type == self::REMEMBER) {
            return $user->getPersistCode();
        }

        if ($type == self::ACTIVATION) {
            return $user->getActivationCode();
        }

        if ($type == self::PASSWORD_RESET) {
            return $user->getResetPasswordCode();
        }

        throw new InvalidArgumentException("Unsupported token type $type");

    }

    /**
     * {@inheritdoc}
     * (No difference with Sentry)
     *
     * @param \Permit\User\UserInterface $user
     * @param int $type (see self::REMEMBER...)
     * @param \DateTime $expiresAt Set a custom expiry date
     * @return string The generated token
     **/
    public function update(User $user, $type, DateTime $expiresAt=null)
    {
        return $this->create($user, $type, $expiresAt);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param string $token
     * @param int $type (see self::REMEMBER...)
     * @return bool
     **/
    public function exists(User $user, $token, $type)
    {

        $this->checkForSentryInterface($user);

        if ($type == self::REMEMBER) {
            return $user->checkPersistCode($token);
        }

        if ($type == self::ACTIVATION) {
            return $user->activation_code == $token;
        }

        if ($type == self::PASSWORD_RESET) {
            return $user->checkResetPasswordCode($token);
        }

        throw new InvalidArgumentException("Unsupported token type $type");

    }

    /**
     * {@inheritdoc}
     *
     * @param string $token
     * @param int $type (see self::REMEMBER...)
     * @return bool
     **/
    public function invalidate($token, $type)
    {

        try {

            if ($type == self::REMEMBER) {
                $user = $this->getByRememberToken($token);
                $user->persist_code = null;
            } elseif ($type == self::ACTIVATION) {
                $user = $this->getByActivationCode($token);
                $this->activation_code = null;
            } elseif ($type == self::PASSWORD_RESET) {
                $user = $this->getByResetPasswordCode($token);
                $user->clearResetPassword();
            }

        } catch (Exception $e) {}

        return true;

    }

    /**
     * {@inheritdoc}
     *
     * @param int $type (see self::REMEMBER...) (optional)
     * @return void
     **/
    public function purgeExpired($type=null)
    {

        throw new RuntimeException("I dont delete tokens in users table");

    }

    protected function getByRememberToken($token)
    {
        $user = $this->userProvider->getEmptyUser()
                                       ->where('persist_code', $token)
                                       ->first();
        if (!$user) {
            throw new TokenNotFoundException("Token $token not found");
        }

        if (!$user->checkPersistCode($token)) {
            throw new TokenInvalidException("Token $token is invalid");
        }

        return $user;
    }

    protected function getByActivationCode($token)
    {
        try {
            $user = $this->userProvider->findByActivationCode($token);
        } catch (UserNotFoundException $e) {
            throw new TokenNotFoundException("Token $token not found");
        } catch (RuntimeException $e) {
            throw new TokenInvalidException("Token $token is invalid");
        }
    }

    protected function getByResetPasswordCode($token)
    {

        try {
            return $this->userProvider->findByResetPasswordCode($token);
        } catch (UserNotFoundException $e) {
            throw new TokenNotFoundException("Token $token not found");
        } catch (RuntimeException $e) {
            throw new TokenInvalidException("Token $token is invalid");
        }


    }

    /**
     * Check the token
     *
     * @param string $token
     * @return void
     **/
    protected function isValid($token){
        return (strlen($activationData['code']) >= 16);
    }

    protected function checkForSentryInterface(UserInterface $user){
        if(!$user instanceof SentryUserInterface){
            throw new InvalidArgumentException("User has to be a valid sentry user instance");
        }
    }

}
