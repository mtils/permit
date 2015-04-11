<?php namespace Permit\Random;

use InvalidArgumentException;

class StrShuffleGenerator implements GeneratorInterface
{

    public $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * {@inheritdoc}
     * @return int
     **/
    public function getStrength()
    {
        return 5;
    }

    /**
     * Checks for functions that do surely exist
     *
     * @return bool
     **/
    public function isSupported()
    {
        return function_exists('str_shuffle');
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

        $charRepeat = max([$length/2, 1]);

        return substr(
            str_shuffle(
                str_repeat($this->chars, $charRepeat)
            ),
            0,
            $length
        );

    }

}