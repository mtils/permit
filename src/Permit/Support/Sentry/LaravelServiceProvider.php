<?php namespace Permit\Support\Sentry;

use Illuminate\Support\ServiceProvider;
use Permit\Support\Sentry\Authenticator;
use Permit\Checker\Checker;
use Permit\CurrentUser\DualContainer;
use Permit\CurrentUser\FallbackContainer;
use Permit\CurrentUser\LoginValidator;
use Permit\Support\Sentry\CurrentUserContainer;
use Permit\Support\Laravel\SessionCurrentUserContainer;
use Permit\Support\Sentry\HolderProvider;
use Permit\AuthService;

class LaravelServiceProvider extends ServiceProvider{

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

        $this->registerAuth();

    }

    protected function registerPermissionChecker(){

        $this->app->singleton('Permit\Checker\CheckerInterface', function($app){
            return new Checker();
        });

    }

    protected function registerLoginValidator(){

        $this->app->singleton('Permit\CurrentUser\LoginValidatorInterface', function($app){
            return new LoginValidator();
        });

    }

    protected function registerCurrentUserContainer(){

        $serviceProvider = $this;

        $this->app->singleton('Permit\CurrentUser\ContainerInterface', function($app) use ($serviceProvider){

            $actualContainer = new CurrentUserContainer($app['sentry']);

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

    protected function createStackedContainer(){

        $userProvider = new HolderProvider($this->app['sentry.user']);

        $stackedContainer = new SessionCurrentUserContainer($this->app['session.store'],
                                                            $userProvider,
                                                            'permissioncode_stacked_user');
        return $stackedContainer;
    }

    protected function createFallbackContainer(){

        return new FallbackContainer();

    }

    protected function registerAuth(){

        $this->app->bindShared('auth', function($app)
        {
            // Once the authentication service has actually been requested by the developer
            // we will set a variable in the application indicating such. This helps us
            // know that we need to set any queued cookies in the after event later.
            $app['auth.loaded'] = true;

            $service = new AuthService(
                $app->make('Permit\CurrentUser\ContainerInterface'),
                $app->make('Permit\Checker\CheckerInterface')
            );

            $service->setFallBack($app['sentry']);

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
            'Permit\Checker\CheckerInterface',
            'Permit\CurrentUser\LoginValidatorInterface'
        ];
    }

}