<?php namespace Permit\Support\Laravel\Providers;


use RuntimeException;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\AuthManager;

use Permit\Registration\RegistrarInterface;
use Permit\Permission\AccessChecker;
use Permit\CurrentUser\DualContainer;
use Permit\CurrentUser\FallbackContainer;
use Permit\CurrentUser\LoginValidator;
use Permit\Support\Laravel\CurrentUser\GuardContainer;
use Permit\Support\Laravel\CurrentUser\SessionContainer;
use Permit\Support\Laravel\User\UserProviderRepository;
use Permit\Authentication\Authenticator;
use Permit\Authentication\CredentialsValidator;
use Permit\Hashing\NativeHasher;
use Permit\Access\FakeAssigner;
use Permit\Registration\Registrar;
use Permit\AuthService;
use Signal\Support\Laravel\IlluminateBus;
use Permit\Throttle\ChecksThrottleOnLogin;
use Permit\Doorkeeper\ChecksBanOnLogin;

/**
 * This service provider is for all developers who used sentry
 * and migrates to permit. It registers all components that they
 * will work with your old tables, users etc.
**/
class SentryLegacyServiceProvider extends ServiceProvider{

    public $useRegistrar = true;

    public $useThrottling = true;

    public $throttleModelClass = 'Permit\Support\Laravel\Throttle\Throttle';

    public $useDoorKeeper = true;

    protected $authManager;

    private $throttleRepoRegistered = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(){

        $this->registerPermissionChecker();

        $this->registerHasher();

        $this->registerUserProvider();

        $this->registerLoginValidator();

        $this->registerGroupRepository();

        $this->registerUserRepository();

    }

    public function boot(){

        $this->registerCurrentUserContainer();

        $this->registerRandomGenerator();

        $this->registerThrottler();

        $this->registerDoorKeeper();

        $this->registerAuthenticator();

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

    protected function getAuthManager()
    {
        if(!$this->authManager){
            $this->authManager = new AuthManager($this->app);
        }
        return $this->authManager;
    }

    protected function getIlluminateGuard()
    {
        return $this->getAuthManager()->driver();
    }

    protected function registerCurrentUserContainer(){

        $serviceProvider = $this;

        $this->app->singleton('Permit\CurrentUser\ContainerInterface', function($app) use ($serviceProvider){

            $actualContainer = new GuardContainer($this->getIlluminateGuard());

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

    protected function registerUserProvider()
    {

        $this->app->singleton('Permit\Authentication\UserProviderInterface', function($app){

            $repo = new UserProviderRepository(
                $this->getIlluminateGuard()->getProvider()
            );

            $app->instance('Permit\User\ProviderInterface', $repo);

            return $repo;

        });

    }

    protected function registerThrottleRepository()
    {

        if ($this->throttleRepoRegistered) {
            return;
        }

        $interface = 'Permit\Throttle\ThrottleRepositoryInterface';

        $this->app->singleton($interface, function($app){
            $class = 'Permit\Support\Laravel\Throttle\ThrottleModelRepository';
            $repo = $app->make($class, [$app->make($this->throttleModelClass)]);
            $app->instance($class, $repo);
            return $repo;
        });

        $this->throttleRepoRegistered = true;
    }

    protected function registerThrottler()
    {

        if (!$this->useThrottling) {
            return;
        }

        $this->registerThrottleRepository();

        $interface = 'Permit\Throttle\ThrottlerInterface';

        $this->app->singleton($interface, function($app){
            return $app->make('Permit\Throttle\Throttler');
        });

        $this->registerThrottleLoginLogger();

    }

    protected function registerThrottleLoginLogger()
    {

        // Just ensure a single instance

        $class = 'Permit\Throttle\ChecksThrottleOnLogin';

        $this->app->singleton($class, function($app){
            return new ChecksThrottleOnLogin(
                $app->make('Permit\Throttle\ThrottlerInterface')
            );
        });
    }

    protected function registerDoorKeeper()
    {

        if (!$this->useDoorKeeper) {
            return;
        }

        $this->registerThrottleRepository();

        $interface = 'Permit\Doorkeeper\DoorkeeperInterface';

        $this->app->singleton($interface, function($app){
            $class = 'Permit\Support\Laravel\Doorkeeper\ThrottleModelDoorkeeper';
            return $app->make($class);
        });

        $this->registerBanLoginChecker();

    }

    protected function registerBanLoginChecker()
    {
        // Just ensure a single instance

        $class = 'Permit\Doorkeeper\ChecksBanOnLogin';

        $this->app->singleton($class, function($app){
            return new ChecksBanOnLogin(
                $app->make('Permit\Doorkeeper\DoorkeeperInterface')
            );
        });
    }

    protected function registerAuthenticator()
    {

        $interface = 'Permit\Authentication\AuthenticatorInterface';

        $this->app->singleton($interface, function($app){
            return $this->createAuthenticator();
        });

    }

    protected function createAuthenticator()
    {

        $this->registerCredentialsValidator();

        $authenticator = new Authenticator(
            $this->app['Permit\Authentication\UserProviderInterface'],
            $this->app['Permit\Authentication\CredentialsValidatorInterface'],
            $this->app['Permit\CurrentUser\ContainerInterface']
        );

        $authenticator->setEventBus(new IlluminateBus($this->app['events']));

        if ($this->useRegistrar) {
             $authenticator->whenAttempted(
                 $this->app['Permit\Registration\Activation\DriverInterface']
             );

        }

        $loginLogger = $this->app->make(
            'Permit\Support\Laravel\Authentication\UserModelLastLoginWriter'
        );

        $authenticator->whenLoggedIn($loginLogger);

        if ($this->useThrottling) {
            $class = 'Permit\Throttle\ChecksThrottleOnLogin';
            $authenticator->whenLoggedIn(
                "$class@recordSucceedLogin"
            );
            $authenticator->whenCredentialsInvalid(
                "$class@recordFailedAttempt"
            );
            $authenticator->whenAttempted(
                "$class@check"
            );
        }

        if ($this->useDoorKeeper) {
            $authenticator->whenAttempted(
                'Permit\Doorkeeper\ChecksBanOnLogin@check'
            );
        }

        return $authenticator;

    }

    protected function registerHasher()
    {
        $this->app->singleton('Permit\Hashing\HasherInterface', function($app){
            return new NativeHasher;

        });
    }

    protected function registerRandomGenerator()
    {
        $this->app->singleton('Permit\Random\GeneratorInterface', function($app){
            $generator = $app->make('Permit\Random\GeneratorSelector');
            $generator->add($app->make('Permit\Random\StrShuffleGenerator'));
            $generator->add($app->make('Permit\Random\McryptGenerator'));
            $generator->add($app->make('Permit\Random\OpenSSLGenerator'));
            return $generator;

        });
    }

    protected function registerCredentialsValidator()
    {
        $this->app->singleton('Permit\Authentication\CredentialsValidatorInterface', function($app){
            return new CredentialsValidator($app['Permit\Hashing\HasherInterface']);

        });
    }

    protected function registerRegistrar()
    {

        $this->registerAccessAssigner();

        if($this->useRegistrar){
            $this->registerActivationDriver();
            $this->registerRegistrarObject();
        }

    }

    protected function registerUserRepository(){

        $this->app->singleton('Permit\Registration\UserRepositoryInterface', function($app){
            return $app->make('Permit\Authentication\UserProviderInterface');

        });

    }

    protected function registerActivationDriver(){

        $this->app->singleton('Permit\Registration\Activation\DriverInterface', function($app){
            return $app->make(
                'Permit\Support\Laravel\Registration\Activation\UserModelDriver',
                [$this->getIlluminateGuard()->getProvider()->createModel()]
            );

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

        $userProvider = $this->app->make('Permit\Authentication\UserProviderInterface');

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

            $service->addFallback($this->getAuthManager());

            return $service;

        });

    }

    protected function registerGroupRepository()
    {
        $interface = 'Permit\Groups\GroupRepositoryInterface';
        $this->app->singleton($interface, function($app){

            $userProvider = $app['Permit\Authentication\UserProviderInterface'];
            $groupModel = $userProvider->newUser()->getGroupModelClass();

            $class = 'Permit\Support\Laravel\Groups\EloquentRepository';
            return $app->make($class, [$app->make($groupModel)]);
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