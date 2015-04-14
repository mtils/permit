<?php namespace Permit\Authentication\Exception;

use RuntimeException;

/**
 * A LoginException is for all errors which occurs during login and are
 * caused by the user (credentials wrong, wrong auth method, user suspended,
 * banned, whatever)
 *
 **/
class LoginException extends RuntimeException
{

    /**
     * @var string
     **/
    protected $reason = '';

    /**
     * Create a login exception with an optional reason
     *
     * @param string $msg
     * @param string $reason
     * @param int $code
     * @see self::getReason()
     **/
    public function __construct($msg='', $reason='', $code=0)
    {
        parent::__construct($msg, $code);
        $this->reason = $reason;
    }

    /**
     * Return a reason (for banning etc)
     *
     * @return string
     **/
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set a reason for this exception
     *
     * @param string $reason
     * @return self
     **/
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

} 
