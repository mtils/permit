<?php namespace Permit\Support\Laravel\Registration;

trait ActivatableByDate
{

    /**
     * The user object has to return if it is activated
     *
     * @return bool
     **/
    public function isActivated()
    {
        return (bool)$this->{$this->getActivatedDateKey()};
    }

    /**
     * Mark the user as activated. There is no way of return if someone is
     * activated. A not activated user is a user in a state between registration
     * and activation. If you need to ban a use, ban him.
     *
     * @return void
     **/
    public function markAsActivated()
    {
        $this->{$this->getActivatedDateKey()} = $this->freshTimestamp();
    }

    /**
     * Returns the key of the activation column
     *
     * @return string
     **/
    public function getActivatedDateKey()
    {
        return 'activated_at';
    }

}