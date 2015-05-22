<?php namespace Permit\Authentication;

use DateTime;
use ArrayAccess;
use RuntimeException;
use Signal\NamedEvent\BusHolderTrait;
use Permit\Registration\ActivatableInterface;
use Permit\Authentication\UserProviderInterface as UserProvider;
use Permit\Token\RepositoryInterface as TokenRepository;
use Permit\Registration\UserRepositoryInterface as UserRepository;
use Permit\User\UserNotFoundException;
use Permit\Token\TokenMissingException;

class CredentialsBroker implements CredentialsBrokerInterface
{

    use BusHolderTrait;

    /**
     * This event is fired if user of request was found before the token
     * is created. ($credentials, $user)
     *
     * @var string
     **/
    public $reservingEvent = 'auth.reserving-passwordreset';

    /**
     * This event is fired when the token was created and saved ($user, $token)
     *
     * @var string
     **/
    public $reservedEvent = 'auth.reserved-passwordreset';

    /**
     * This event is fired when the user updates its credentials before save
     * This happens on reset() and update(). ($credentials, $user)
     *
     * @var string
     **/
    public $resettingEvent = 'auth.resetting-password';

    /**
     * This event is fired when the password reset or update is completed.
     * ($credentials, $password)
     *
     * @var string
     **/
    public $resettedEvent = 'auth.resetted-password';

    /**
     * The password key in credentials array
     *
     * @var string
     **/
    public $passwordKey = 'password';

    /**
     * The token key in credentials array
     *
     * @var string
     **/
    public $tokenKey = 'token';

    /**
     * @var \Permit\Authentication\UserProviderInterface
     **/
    protected $userProvider;

    /**
     * @var \Permit\Token\RepositoryInterface
     **/
    protected $tokens;

    /**
     * @var \Permit\Registration\UserRepositoryInterface
     **/
    protected $users;

    /**
     * @var int
     **/
    protected $expiryMinutes = 60;

    /**
     * @var callable
     **/
    protected $credentialsSetter;

    /**
     * @var callable
     **/
    protected $expiryCalculator;

    /**
     * @var \DateTime
     **/
    protected $now;

    /**
     * @param \Permit\Authentication\UserProviderInterface $userProvider
     * @param \Permit\Token\RepositoryInterface $tokenRepository
     * @param \Permit\Registration\UserRepositoryInterface $userRepository
     **/
    public function __construct(UserProvider $userProvider,
                                TokenRepository $tokenRepository,
                                UserRepository $userRepository)
    {
        $this->userProvider = $userProvider;
        $this->tokens = $tokenRepository;
        $this->users = $userRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $credentials
     * @param callable $thenDo (optional)
     * @return string The generated $token
     **/
    public function reserveReset(array $credentials, callable $thenDo=null)
    {

        $user = $this->findUserOrFail($credentials);

        $this->fire($this->reservingEvent, [$credentials, $user]);

        $token = $this->tokens->create(
            $user,
            TokenRepository::PASSWORD_RESET,
            $this->getExpiresAt()
        );

        $this->fire($this->reservedEvent, [$user, $token]);

        if (is_callable($thenDo)) {
            call_user_func_array($thenDo, [$user, $token]);
        }


        return $token;

    }

    /**
     * {@inheritdoc}
     *
     * @param array $credentials
     * @return \Permit\User\UserInterface
     **/
    public function reset(array $credentials)
    {

        if (!isset($credentials[$this->tokenKey])) {
            throw new TokenMissingException("Token not passed");
        }

        $token = $credentials[$this->tokenKey];

        $authId = $this->tokens->getAuthIdByToken(
            $token,
            TokenRepository::PASSWORD_RESET
        );

        $user = $this->findUserByIdOrFail($authId);

        $this->update($user, $credentials);

        $this->tokens->invalidate($user, TokenRepository::PASSWORD_RESET, $token);

        return $user;

    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\Registration\ActivatableInterface $user
     * @param $credentials
     * @return bool
     **/
    public function update(ActivatableInterface $user, array $credentials)
    {

        $this->fire($this->resettingEvent, [$credentials, $user]);

        $this->applyCredentials($user, $credentials);

        $result = $this->users->save($user);

        $this->fire($this->resettedEvent, [$credentials, $user]);

        return $result;

    }

    /**
     * {@inheritdoc}
     *
     * @param callable $credentialsSetter
     * @return self
     **/
    public function setCredentialsBy(callable $credentialsSetter)
    {
        $this->credentialsSetter = $credentialsSetter;
        return $this;
    }

    /**
     * Return the minutes a token will be valid (default 60)
     *
     * @return int
     **/
    public function getExpiryMinutes()
    {
        return $this->expiryMinutes;
    }

    /**
     * Set the minutes a token will be valid
     *
     * @param int $minutes
     * @return self
     **/
    public function setExpiryMinutes($minutes)
    {
        $this->expiryMinutes = $minutes;
        return $this;
    }

    /**
     * Calculate the expiry date
     *
     * @return \DateTime
     **/
    public function getExpiresAt()
    {

        if ($calc = $this->expiryCalculator) {
            return $calc($this->now(), $this->getExpiryMinutes());
        }

        if (!$expiryMinutes = $this->getExpiryMinutes()) {
            return null;
        }

        return $this->now()->modify("+$expiryMinutes minutes");

    }

    /**
     * Set a custom expiry calculator
     *
     * @param callable
     * @return self
     **/
    public function calculateExpiryWith(callable $expiryCalc)
    {
        $this->expiryCalculator = $expiryCalc;
        return $this;
    }

    /**
     * Apply the credentials on the user object. This methods guesses if you do
     * not assign a setter via setCredentialsBy
     *
     * @param \Permit\Registration\ActivatableInterface $user
     * @param $credentials
     * @return void
     **/
    protected function applyCredentials(ActivatableInterface $user, array $credentials)
    {

        $credentials = $this->filterCredentials($credentials);

        // If a manual setter was assigned use it
        if ($setter = $this->credentialsSetter) {
            return $setter($user, $credentials);
        }

        // If this is kindof property overloading object use __set
        if (method_exists($user,'__set')) {
            foreach ($credentials as $key=>$value) {
                $user->__set($key, $value);
            }
            return;
        }

        // If it supports array access
        if ($user instanceof ArrayAccess) {
            foreach ($credentials as $key=>$value) {
                $user[$key] = $value;
            }
            return;
        }

        throw new RuntimeException('Unable to apply credentials to user. Assign a callable to do it yourself');

    }

    /**
     * Find the user by credentials or throw an exception
     *
     * @param array $credentials
     * @return \Permit\Registration\ActivatableInterface
     **/
    protected function findUserOrFail(array $credentials)
    {

        if ($user = $this->userProvider->findByCredentials($credentials)) {
            return $user;
        }

        throw new UserNotFoundException();
    }

    /**
     * Find the user by auth id or throw an exception
     *
     * @param mixed $authId
     * @return \Permit\Registration\ActivatableInterface
     **/
    protected function findUserByIdOrFail($authId)
    {

        if ($user = $this->users->retrieveByAuthId($authId)) {
            return $user;
        }

        throw new UserNotFoundException();
    }

    /**
     * Filter out any keys that does surely not remain in the user object
     *
     * @param array $credentials
     * @return array
     **/
    protected function filterCredentials(array $credentials)
    {

        $filtered = [];

        foreach ($credentials as $key=>$value) {

            if ($key == $this->tokenKey) {
                continue;
            }

            if ($key == $this->passwordKey.'_confirmation') {
                continue;
            }

            if (strpos($key,'_') === 0) {
                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * Return the current DateTime
     *
     * @return \DateTime
     **/
    protected function now()
    {
        if ($this->now) {
            return clone $this->now;
        }

        return new DateTime;
    }

    /**
     * Modify the current datetime for testing purposes
     *
     * @param \DateTime $now
     * @return self
     **/
    public function setNow(DateTime $now)
    {
        $this->now = $now;
        return $this;
    }

}