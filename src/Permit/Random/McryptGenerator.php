<?php namespace Permit\Random;

use InvalidArgumentException;

class MCryptGenerator implements GeneratorInterface{

    /**
     * {@inheritdoc}
     * @return int
     **/
    public function getStrength()
    {
        return 25;
    }

    /**
     * Checks for openssl support
     *
     * @return bool
     **/
    public function isSupported()
    {
        return function_exists('mcrypt_create_iv');
    }

    /**
     * Generate a random string with openssl
     *
     * @param int $length
     * @param bool $asciiOnly
     * @return string
     **/
    public function generate($length=42, $asciiOnly=True)
    {

        if($length < 1){
            throw new InvalidArgumentException("Length has to be > 0");
        }

        if ($asciiOnly) {
            $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            return substr(bin2hex($bytes), 0, $length);
        }

        return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
    }

}