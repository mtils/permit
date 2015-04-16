<?php namespace Permit\Authentication;


use OutOfBoundsException;

use Permit\Hashing\HasherInterface;
use Permit\User\UserInterface;

class CredentialsValidatorChain implements CredentialsValidatorInterface
{

    /**
     * @var array
     **/
    protected $validators = [];

    public function add(CredentialsValidatorInterface $validator)
    {
        $this->validators[] = $validator;
        return $this;
    }

    public function remove(CredentialsValidatorInterface $validator)
    {
        $this->validators = array_filter(
            $this->validators,
            function($added) use ($validator) {
                return ($added !== $validator)
            }
        );
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


}