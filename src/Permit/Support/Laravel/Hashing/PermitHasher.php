<?php namespace Permit\Support\Laravel\Hashing;


use Illuminate\Contracts\Hashing\Hasher as IlluminateHasher;

use Permit\Hashing\HasherInterface as PermitContract;

/**
 * A simple proxy to use a permit hasher in your laravel application
 **/
class PermitHasher implements IlluminateHasher
{

    /**
     * @var \Permit\Hashing\HasherInterface
     **/
    protected $permitHasher;

    /**
     * @param \Permit\Hashing\HasherInterface $permitHasher
     **/
    public function __construct(PermitContract $permitHasher)
    {
        $this->permitHasher = $permitHasher;
    }

    /**
     * Hash the given value.
     *
     * @param  string  $value
     * @param  array   $options
     * @return string
     */
    public function make($value, array $options = array())
    {
        return $this->permitHasher->hash($value, $options);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = array())
    {
        return $this->permitHasher->check($value, $hashedValue, $options);
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = array())
    {
        return $this->permitHasher->needsRehash($hashedValue, $options);
    }

    public function info($hashedValue)
    {
        return password_get_info($hashedValue);
    }


}