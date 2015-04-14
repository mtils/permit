<?php namespace Permit\Hashing;

use RuntimeException;

class NativeHasher implements HasherInterface
{

    /**
     * @var int
     **/
    protected $algorithm = PASSWORD_DEFAULT;

    /**
     * @var bool
     **/
    private $supported;

    /**
     * @var Permit\Hashing\NativeHasherSystemAdapter
     **/
    protected $systemAdapter;

    public function __construct(NativeHasherSystemAdapter $adapter=null)
    {
        $this->systemAdapter = $adapter ?: new NativeHasherSystemAdapter;
    }

    /**
     * Hash the given plain value.
     *
     * @param  string  $plain
     * @param  array   $options
     * @return string
     */
    public function hash($plain, array $options = [])
    {

        $this->checkSystemSupport();

        $hash = $this->systemAdapter->passwordHash($plain, $this->algorithm);

        if ($hash === false) {
            throw new RuntimeException('Error generating hash from string, your PHP environment is probably incompatible. Try running [vendor/ircmaxell/password-compat/version-test.php] to check compatibility or use an alternative hashing strategy.');
        }

        return $hash;

    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $plain
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function check($plain, $hashedValue, array $options = [])
    {
        $this->checkSystemSupport();
        return $this->systemAdapter->passwordVerify($plain, $hashedValue);
    }

    /**
     * Check if the given hash has to be rehashed to match the given options
     *
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        $this->checkSystemSupport();
        return $this->systemAdapter->passwordNeedsRehash(
            $hashedValue,
            $this->algorithm,
            $options
        );
    }

    /**
     * Proves and caches system support
     *
     * @return bool
     **/
    protected function checkSystemSupport()
    {
        if ($this->supported === NULL){
            $this->supported = $this->systemAdapter->isSupported();
        }

        if (!$this->supported) {
            throw new RuntimeException('This version PHP does not support password_hash()');
        }
    }

}

/**
 * This class is just to simplify testing
 **/
class NativeHasherSystemAdapter{

    public function isSupported()
    {
        return (
                function_exists('password_hash') &&
                function_exists('password_verify') &&
                function_exists('password_needs_rehash')
        );
    }

    public function passwordHash($password, $algorithm, $options=[])
    {
        return password_hash($password, $algorithm, $options);
    }

    public function passwordVerify($password, $hash){
        return password_verify($password, $hash);
    }

    public function passwordNeedsRehash($hash, $algorithm, $options)
    {
        return password_needs_rehash($hash, $algorithm, $options);
    }
}