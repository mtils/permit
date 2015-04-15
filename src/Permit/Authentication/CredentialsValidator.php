<?php namespace Permit\Authentication;


use OutOfBoundsException;

use Permit\Hashing\HasherInterface;
use Permit\User\UserInterface;

class CredentialsValidator implements CredentialsValidatorInterface
{

    /**
     * @var string
     **/
    protected $passwordKey = 'password';

    /**
     * @var Permit\Hashing\HasherInterface
     **/
    protected $hasher;

    /**
     * @param $hasher \Permit\Hashing\HasherInterface
     **/
    public function __construct(HasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user
     * @param array $credentials
     * @return bool
     **/
    public function validateCredentials(UserInterface $user, array $credentials)
    {
        if (!isset($credentials[$this->passwordKey])) {
            return false;
        }

        $password = $credentials[$this->passwordKey];
        $hashedPassword = $this->getPasswordFromUser($user);

        return $this->hasher->check($password, $hashedPassword);

    }

    /**
     * Returns the password of passed user $user
     *
     * @param Permit\User\UserInterface
     * @return string The hashed password
     **/
    protected function getPasswordFromUser(UserInterface $user)
    {
        if (method_exists($user,'getPassword')) {
            return $user->getPassword();
        }

        if (method_exists($user,'getAuthPassword')) {
            return $user->getAuthPassword();
        }

        if (isset($user->password)) {
            return $user->password;
        }

        throw new OutOfBoundsException("Could not determine users password attribute");
    }

}