<?php namespace Permit\Hashing;

interface HasherInterface{

    /**
     * Hash the given plain value.
     *
     * @param  string  $plain
     * @param  array   $options
     * @return string
     */
    public function hash($plain, array $options = []);

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $plain
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function check($plain, $hashedValue, array $options = []);

    /**
     * Check if the given hash has to be rehashed to match the given options
     *
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = []);

}