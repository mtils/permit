<?php namespace Permit\Authentication;


use InvalidArgumentException;

use Permit\Authentication\Exception\CredentialsNotFoundException;
use Permit\Authentication\Exception\CredentialsInvalidException;
use Permit\CurrentUser\ContainerInterface;
use Permit\CurrentUser\CanRememberUser;
use Permit\User\UserInterface;
use Ems\Core\Patterns\HookableTrait;

class Authenticator implements AuthenticatorInterface
{

    use HookableTrait;

    /**
     * @var \Permit\Authentication\UserProviderInterface
     **/
    protected $userProvider;

    /**
     * @var \Permit\Authentication\CredentialsValidatorInterface
     **/
    protected $credentialsValidator;

    /**
     * @var \Permit\CurrentUser\ContainerInterface
     **/
    protected $userContainer;

    public function __construct(UserProviderInterface $userProvider,
                                CredentialsValidatorInterface $validator,
                                ContainerInterface $container)
    {
        $this->userProvider = $userProvider;
        $this->credentialsValidator = $validator;
        $this->userContainer = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $credentials The (request) params (login/password, whatever)
     * @param $remember Create a remember token
     * @return \Permit\User\UserInterface;
     * @throws \Permit\Authentication\Exception\LoginException
     **/
    public function authenticate(array $credentials, $remember=true)
    {

        if ($remember && !($this->userContainer instanceof CanRememberUser)) {
            throw new InvalidArgumentException('Container does not support remembering');
        }

        $this->callBeforeListeners('authenticate', [$credentials, $remember]);

        if (!$user = $this->userProvider->findByCredentials($credentials)) {

            $this->callAfterListeners('credentialsNotFound', [$credentials, $remember]);

            throw new CredentialsNotFoundException('User not found');

        }

        if (!$this->credentialsValidator->validateCredentials($user, $credentials)) {

            $this->callAfterListeners('credentialsInvalid', [$user, $credentials, $remember]);

            throw new CredentialsInvalidException('Credentials wrong');
        }

        $this->callAfterListeners('authenticate', [$user, $credentials, $remember]);

        $this->loginUser($user, $remember);

        return $user;

    }

    /**
     * Logs the user in without the credential checks
     *
     * @param \Permit\User\UserInterface $user
     * @param bool $remember
     * @return \Permit\User\UserInterface
     **/
    public function loginUser(UserInterface $user, $remember=true)
    {
        $this->callBeforeListeners('loginUser', [$user, $remember]);

        $this->putIntoContainer($user, $remember);

        $this->callAfterListeners('loginUser', [$user, $remember]);

        return $user;

    }

    /**
     * Puts the user into the session container
     *
     * @param \Permit\User\UserInterface $user
     * @param bool $remember
     * @return void
     **/
    protected function putIntoContainer(UserInterface $user, $remember)
    {
        if($remember){
            $this->userContainer->setAndRemember($user);
            return;
        }

        $this->userContainer->setUser($user);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Permit\User\UserNotFoundException
     **/
    public function logout()
    {

        $user = $this->userContainer->user();

        $this->callBeforeListeners('logout', [$user]);

        $this->userContainer->clearUser();

        $this->callAfterListeners('logout', [$user]);

        return $user;

    }

    /**
     * Hook into before the login process begins. 
     * 
     * Params passed to the callable:
     * (array $credentials, bool $remember)
     *
     * What you could do here:
     * - Block IP-Adresses or browser-fingerprints on too many attempts
     * - Log every attempt
     * - Check if some other precautions like csrf tokens are present
     * ..
     *
     * @param callable $callable
     * @return void
     **/
    public function whenAttempting($callable)
    {
        $this->onBefore('authenticate', $callable);
    }

    /**
     * Get informed when the user was not found by the passed credentials
     *
     * Params passed to the callable:
     * (array $credentials, bool $remember)
     *
     * What you could do here:
     * - Block IP-Adresses or browser-fingerprints on too many wrong attempts
     * - Log every invalid attempt
     * ..
     *
     * @param callable $callable
     * @return void
     **/
    public function whenCredentialsNotFound($callable)
    {
        $this->onAfter('credentialsNotFound', $callable);
    }

    /**
     * Get informed when the user was found but the credentials are invalid
     *
     * Params passed to the callable:
     * (UserInterface $user, array $credentials, bool $remember)
     *
     * What you could do here:
     * - Block User accounts (be careful with that)
     * - Send a mail to the user on many wrong attempts (better)
     * - Log every invalid attempt related to the user (e.g. throttling)
     * ..
     *
     * Security Advice: Dont allow a user to block the account of a different
     * user by repeatingly passing wrong credentials
     *
     * @param callable $callable
     * @return void
     **/
    public function whenCredentialsInvalid($callable)
    {
        $this->onAfter('credentialsInvalid', $callable);
    }

    /**
     * Hook into that action to be informed after successful validating
     * credentials but before the actual login
     *
     * Params passed to the callable:
     * (UserInterface $user, array $credentials, bool $remember)
     *
     * What you could do here:
     * - Check if the user is suspended, blocked, etc. and throw a LoginException
     * - Log every invalid attempt related to the user
     * - Rehash the password
     * - ...
     *
     * @param callable $callable
     * @return void
     **/
    public function whenAttempted($callable)
    {
        $this->onAfter('authenticate', $callable);
    }

    /**
     * Get informed if a user was successfully logged in
     *
     * Params passed to the callable:
     * (UserInterface $user, bool $remember)
     *
     * What you could do here:
     * - Log user login (if you want only the last date in user table or so)
     * - statistics
     * - ...
     *
     * @param callable $callable
     * @return void
     **/
    public function whenLoggedIn($callable)
    {
        $this->onAfter('loginUser', $callable);
    }

    /**
     * Get informed if a user tries to log out
     *
     * Params passed to the callable:
     * (UserInterface $user)
     *
     * What you could do here:
     * - Clean up any session based stuff
     * - statistics
     * - ...
     *
     * @param callable $callable
     * @return void
     **/
    public function whenLoggingOut($callable)
    {
        $this->onBefore('logout', $callable);
    }

    /**
     * Get informed if a user was logged out
     *
     * Params passed to the callable:
     * (UserInterface $user)
     *
     * What you could do here:
     * - Clean up any session based stuff
     * - statistics
     * - write last_login col or login history
     * - ...
     *
     * @param callable $callable
     * @return void
     **/
    public function whenLoggedOut($callable)
    {
        $this->onAfter('logout', $callable);
    }

}
