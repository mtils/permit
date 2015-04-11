<?php namespace Permit\Random;

use InvalidArgumentException;

class OpenSSLGenerator implements GeneratorInterface{

    /**
     * {@inheritdoc}
     * @return int
     **/
    public function getStrength()
    {
        return 20;
    }

    /**
     * Checks for openssl support
     *
     * @return bool
     **/
    public function isSupported()
    {
        return function_exists('openssl_random_pseudo_bytes');
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

        $cstrong=true;

        if ($asciiOnly) {
            $bytes = openssl_random_pseudo_bytes($length, $cstrong);
            return substr(bin2hex($bytes), 0, $length);
        }

        return openssl_random_pseudo_bytes($length, $cstrong);
    }

}