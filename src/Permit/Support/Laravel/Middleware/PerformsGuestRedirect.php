<?php


namespace Permit\Support\Laravel\Middleware;


abstract class PerformsGuestRedirect
{

    public $redirectPath = 'auth/login';

    /**
     * @var callable
     **/
    protected $redirectProvider;

    /**
     * @param $user
     * @param array $permissions
     * @return Illuminate\Http\Response
     **/
    protected function guestRedirect($user, $permissions)
    {
        if (!$this->redirectProvider) {
            return redirect()->guest($this->redirectPath);
        }
        return call_user_func($this->redirectProvider, $user, $permissions);
    }

    /**
     * Pass a callable which will perform the redirect
     *
     * @param callable $provider
     * @return self
     **/
    public function provideRedirect(callable $provider)
    {
        $this->redirectProvider = $provider;
        return $this;
    }

}