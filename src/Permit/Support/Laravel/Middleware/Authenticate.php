<?php namespace Permit\Support\Laravel\Middleware;

use Closure;
use Permit\CurrentUser\ContainerInterface as Auth;

class Authenticate {

    public $redirectPath = 'auth/login';

    /**
     * The user container
     *
     * @var \Permit\CurrentUser\ContainerInterface
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  \Permit\CurrentUser\ContainerInterface  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->user()->isGuest())
        {
            if ($request->ajax())
            {
                return response('Unauthorized.', 401);
            }
            else
            {
                return redirect()->guest($this->redirectPath);
            }
        }

        return $next($request);
    }

}
