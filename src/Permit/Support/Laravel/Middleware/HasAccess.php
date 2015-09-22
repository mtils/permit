<?php namespace Permit\Support\Laravel\Middleware;

use Closure;
use Permit\CurrentUser\ContainerInterface as Auth;
use Permit\Access\CheckerInterface as AccessChecker;
use RuntimeException;

class HasAccess extends PerformsGuestRedirect
{

    /**
     * The current user container.
     *
     * @var \Permit\CurrentUser\ContainerInterface
     */
    protected $auth;


    /**
     * @var \Pertmit\Access\CheckerInterface
     **/
    protected $checker;

    /**
     * Create a new filter instance.
     *
     * @param  \Permit\CurrentUser\ContainerInterface  $auth
     * @return void
     */
    public function __construct(Auth $auth, AccessChecker $checker)
    {
        $this->auth = $auth;
        $this->checker = $checker;
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

        $permissions = $this->retrievePermissionsFromRequest($request);

        $user = $this->auth->user();

        if (!$this->checker->hasAccess($user, $permissions)) {

            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return $this->guestRedirect($user, $permissions);
            }

        }

        return $next($request);
    }

    protected function retrievePermissionsFromRequest($request)
    {

        if (!$route = $request->route()) {
            throw new RuntimeException('Current route not found in request');
        }

        if (!$action = $route->getAction()) {
            throw new RuntimeException('Action of route not set');
        }

        if (!isset($action['permission'])) {
            throw new RuntimeException('No permissions set on your route');
        }

        return (array)$action['permission'];
    }

}
