<?php namespace Permit\Token;

use Permit\Random\GeneratorInterface;

trait GeneratesTokens
{

    /**
     * Set the length of the tokens
     *
     * @var array
     **/
    protected $tokenLengths = [
        RepositoryInterface::REMEMBER          => 60,
        RepositoryInterface::ACTIVATION        => 60,
        RepositoryInterface::PASSWORD_RESET    => 60,
        RepositoryInterface::OAUTH             => 60
    ];

    /**
     * @var \Permit\Random\GeneratorInterface
     **/
    protected $randomGenerator;

    /**
     * Checks the token (by its length)
     *
     * @param string
     * @return bool
     **/
    public function isValid($token, $type)
    {
        return (strlen($token) == $this->getTokenLength($type));
    }

    /**
     * Return the token length for tokens of type $type
     *
     * @param int $type (see \Permit\Token\RepositoryInterface::REMEMBER...)
     * @return int
     **/
    public function getTokenLength($type)
    {
        return $this->tokenLengths[$type];
    }

    /**
     * Set the token length for token of type $type. If you dont pass the
     * type, all lengths will be set
     *
     * @param int $length
     * @param int $type (see \Permit\Token\RepositoryInterface::REMEMBER...)
     * @return self
     **/
    public function setTokenLength($length, $type=null)
    {
        if ($type) {
            $this->tokenLengths[$type] = $length;
            return $this;
        }

        foreach ($this->tokenLengths as $type=>$oldLength) {
            $this->tokenLengths[$type] = $length;
        }

        return $this;
    }

    /**
     * @return \Permit\Random\GeneratorInterface
     **/
    public function getRandomGenerator()
    {
        return $this->randomGenerator;
    }

    /**
     * Set a random generator
     *
     * @param \Permit\Random\GeneratorInterface $generator
     * @return self
     **/
    public function setRandomGenerator(GeneratorInterface $generator)
    {
        $this->randomGenerator = $generator;
        return $this;
    }

    /**
     * Generates a token of type $type
     *
     * @param int $type (see \Permit\Token\RepositoryInterface::REMEMBER...)
     * @return string
     **/
    protected function generateToken($type)
    {
        return $this->randomGenerator->generate($this->getTokenLength($type));
    }

}