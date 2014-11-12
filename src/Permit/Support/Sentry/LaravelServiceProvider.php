<?php namespace Permit\Support\Sentry;

use Illuminate\Support\ServiceProvider;
use Permit\Support\Sentry\Authenticator;
use Permit\Registration\RegistrarInterface;
use Permit\Permission\AccessChecker;
use Permit\CurrentUser\DualContainer;
use Permit\CurrentUser\FallbackContainer;
use Permit\CurrentUser\LoginValidator;
use Permit\Support\Sentry\CurrentUser\Container;
use Permit\Support\Laravel\CurrentUser\SessionContainer;
use Permit\Support\Sentry\User\Provider as UserProvider;
use Permit\Support\Sentry\Registration\UserRepository;
use Permit\Support\Sentry\Registration\Activation\Driver;
use Permit\Access\FakeAssigner;
use Permit\Registration\Registrar;
use Permit\AuthService;

class LaravelServiceProvider extends ServiceProvider{

    public $useRegistrar = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(){

        $this->registerPermissionChecker();

        $this->registerLoginValidator();

    }

    public function boot(){

        $this->registerCurrentUserContainer();

        $this->registerRegistrar();

        $this->registerAuth();

    }

    protected function registerPermissionChecker(){

        $this->app->singleton('Permit\Access\CheckerInterface', function($app){
            return new AccessChecker();
        });

    }

    protected function registerLoginValidator(){

        $this->app->singleton('Permit\CurrentUser\LoginValidatorInterface', function($app){
            return $app->make('Permit\CurrentUser\LoginValidator');
        });

    }

    protected function registerCurrentUserContainer(){

        $serviceProvider = $this;

        $this->app->singleton('Permit\CurrentUser\ContainerInterface', function($app) use ($serviceProvider){

            $actualContainer = new Container($app['sentry']);

            $dualContainer = new DualContainer(
                $actualContainer,
                $app->make('Permit\CurrentUser\LoginValidatorInterface')
            );

            if($stackedContainer = $serviceProvider->createStackedContainer()){
                $dualContainer->setStackedContainer($stackedContainer);
            }

            if($fallbackContainer = $serviceProvider->createFallbackContainer()){
                $dualContainer->setFallbackContainer($fallbackContainer);
            }

            return $dualContainer;

        });

        $this->app->singleton('Permit\CurrentUser\DualContainerInterface', function($app){

            return $app->make('Permit\CurrentUser\ContainerInterface');

        });

    }

    protected function registerRegistrar(){

        if($this->useRegistrar){

            $this->registerUserRepository();
            $this->registerActivationDriver();
            $this->registerAccessAssigner();
            $this->registerRegistrarObject();
        }

    }

    protected function registerUserRepository(){

        $this->app->singleton('Permit\Registration\UserRepositoryInterface', function($app){
            return new UserRepository($app['sentry.user']);

        });

    }

    protected function registerActivationDriver(){

        $this->app->singleton('Permit\Registration\Activation\DriverInterface', function($app){
            return new Driver();

        });

    }

    protected function registerAccessAssigner(){

        $this->app->singleton('Permit\Access\AssignerInterface', function($app){
            return new FakeAssigner();

        });

    }

    protected function registerRegistrarObject(){

        // All interfaces are binded, so just make it
        $this->app->singleton('Permit\Registration\RegistrarInterface', function($app){

            $registrar = $app->make('Permit\Registration\Registrar');

            $registrar->setEventDispatcher($app['events']);

            return $registrar;

        });

    }

    protected function createStackedContainer(){

        $userProvider = new UserProvider($this->app['sentry.user']);

        $stackedContainer = new SessionContainer($this->app['session.store'],
                                                 $userProvider,
                                                 'permissioncode_stacked_user');
        return $stackedContainer;
    }

    protected function createFallbackContainer(){

        return new FallbackContainer();

    }

    protected function registerAuth(){

        $useRegistrar = $this->useRegistrar;

        $this->app->bindShared('auth', function($app) use ($useRegistrar)
        {
            // Once the authentication service has actually been requested by the developer
            // we will set a variable in the application indicating such. This helps us
            // know that we need to set any queued cookies in the after event later.
            $app['auth.loaded'] = true;

            $service = new AuthService(
                $app->make('Permit\CurrentUser\ContainerInterface'),
                $app->make('Permit\Access\CheckerInterface')
            );

            if($useRegistrar){
                $service->setRegistrar($app->make('Permit\Registration\RegistrarInterface'));
            }

            $service->addFallBack($app['sentry']);

            return $service;

        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'auth',
            'Permit\CurrentUser\ContainerInterface',
            'Permit\Access\CheckerInterface',
            'Permit\CurrentUser\LoginValidatorInterface'
        ];
    }

}