<?php namespace Permit\Random;

interface GeneratorInterface
{

    /**
     * Returns the estimated strength of this generator,
     * should be between 0 and 100
     *
     * @return int
     **/
    public function getStrength();

    /**
     * Returns if this generator is supported by the system
     *
     * @return bool
     **/
    public function isSupported();

    /**
     * Generate a random string with strlen $length
     *
     * @param int $length
     * @param bool $asciiOnly
     * @return string
     **/
    public function generate($length=42, $asciiOnly=True);

}