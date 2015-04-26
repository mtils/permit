<?php namespace Permit\Support\Laravel\User;

/**
 * Use this trait in your user if you use the token repository. This allows
 * to store the remember token temporary in the user objects for laravels Guard
 * and mimics its behaviour.
 **/
trait RemembersOnlyTemporary
{

    /**
     * @var string
     **/
    protected $rememberToken;

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->rememberToken = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return '';
    }

}