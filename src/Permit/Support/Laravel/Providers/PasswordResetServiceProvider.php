<?php namespace Permit\Support\Laravel\Providers;

use Illuminate\Auth\Passwords\PasswordResetServiceProvider as IlluminateProvider;

class PasswordResetServiceProvider extends IlluminateProvider
{

    protected function registerTokenRepository()
    {
        $this->app->singleton('auth.password.tokens', function($app)
        {

            $expire = $app['config']->get('auth.password.expire', 60);

            $class = 'Permit\Support\Laravel\Token\PasswordResetTokenRepository';

            return $app->make($class,[
                $app->make('Permit\Token\RepositoryInterface'),
                $app->make('Permit\User\ProviderInterface'),
                $expire
            ]);

        });

        $this->registerPermitBroker();
    }

    protected function registerPermitBroker()
    {
        $interface = 'Permit\Authentication\CredentialsBrokerInterface';
        $class = 'Permit\Authentication\CredentialsBroker';
        $this->app->alias('permit.credentials-broker', $interface);

        $this->app->singleton('permit.credentials-broker', function($app) use ($class){

            $expire = $app['config']->get('auth.password.expire', 60);
            $broker = $app->make($class);
            $broker->setExpiryMinutes($expire);

            return $broker;

        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        $provides = parent::provides();
        $provides[] = 'permit.credentials-broker';
        $provides[] = 'Permit\Authentication\CredentialsBrokerInterface';
        return $provides;
    }

}