<?php namespace Permit\Registration;

use Permit\User\GenericUser as BaseUser;

class GenericUser extends BaseUser implements ActivatableInterface
{

    /**
     * @var bool
     **/
    protected $activated = false;

    /**
     * The user object has to return if it is activated
     *
     * @return bool
     **/
    public function isActivated()
    {
        return $this->activated;
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
        $this->activated = true;
    }

}