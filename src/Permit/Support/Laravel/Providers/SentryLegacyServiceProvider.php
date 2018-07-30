<?php namespace Permit\Support\Laravel\Providers;


use RuntimeException;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\AuthManager;

use Permit\Registration\RegistrarInterface;
use Permit\Permission\AccessChecker;
use Permit\Access\CheckerChain;
use Permit\CurrentUser\DualContainer;
use Permit\CurrentUser\FallbackContainer;
use Permit\CurrentUser\LoginValidator;
use Permit\Support\Laravel\CurrentUser\GuardContainer;
use Permit\Support\Laravel\CurrentUser\SessionContainer;
use Permit\Authentication\Authenticator;
use Permit\Authentication\CredentialsValidator;
use Permit\Hashing\NativeHasher;
use Permit\Access\FakeAssigner;
use Permit\Registration\Registrar;
use Permit\AuthService;
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

    public $userProviderClass = 'Permit\Support\Laravel\User\EloquentUserProvider';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->registerPermissionMerger();

        $this->registerPermissionChecker();

        $this->registerHasher();

        $this->registerTokenRepository();

        $this->registerUserProvider();

        $this->registerLoginValidator();

        $this->registerGroupRepository();

        $this->registerGroupManager();

        $this->registerUserRepository();

        $this->app->singleton('auth.driver', function($app)
        {
            return $app['auth']->guard();
        });

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

    protected function registerPermissionMerger()
    {

        $this->app->singleton('Permit\Permission\MergerInterface', function($app){

            $merger = $app->make('Permit\Permission\NoWildcardMerger');

            return $app->make(
                'Permit\Permission\CachedMerger',
                ['merger' => $merger]
            );

        });
    }

    protected function registerPermissionChecker()
    {

        $this->app->singleton('Permit\Access\CheckerInterface', function($app) {

            $chain = new CheckerChain();

            $chain->provideCheckers(function($chain) {
                $this->addAccessCheckers($chain);
            });

            return $chain;

        });

    }

    protected function addAccessCheckers(CheckerChain $chain)
    {

        $this->addCodePermissionChecker($chain);

        $this->addUserToUserPermissionChecker($chain);
    }

    protected function addCodePermissionChecker(CheckerChain $checkerChain)
    {
        $checkerChain->addChecker(
            $this->app->make('Permit\Permission\MergedChecker')
        );
    }

    protected function addUserToUserPermissionChecker(CheckerChain $checkerChain)
    {
        $checkerChain->addChecker(
            $this->app->make('Permit\Permission\CanAccessHolderChecker')
        );
    }

    protected function registerLoginValidator(){

        $this->app->singleton('Permit\CurrentUser\LoginValidatorInterface', function($app){
            return $app->make('Permit\CurrentUser\LoginValidator');
        });

    }

    protected function registerTokenRepository()
    {
        $this->app->singleton('Permit\Token\RepositoryInterface', function($app){

            $userModel = $app->make($app['config']['auth.model']);
            $repoClass = 'Permit\Support\Laravel\Token\UserModelTokenRepository';

            $repo = $app->make($repoClass, ['model' => $userModel]);
            $repo->rememberKey = 'persist_code';

            return $repo;

        });
    }

    protected function getAuthManager()
    {
        if(!$this->authManager){
            $this->authManager = new AuthManager($this->app);
            $this->authManager->provider('permit', function($app){
                $userModel = $app->make($app['config']['auth.model']);
                return $app->make($this->userProviderClass, ['modelInstance' => $userModel]);
            });

        }
        return $this->authManager;
    }

    protected function getIlluminateGuard()
    {
        return $this->getAuthManager()->guard();
    }

    protected function registerCurrentUserContainer(){

        $this->app->singleton('Permit\CurrentUser\ContainerInterface', function($app) {

            $actualContainer = new GuardContainer($this->getIlluminateGuard());

            $dualContainer = new DualContainer(
                $actualContainer,
                $app->make('Permit\CurrentUser\LoginValidatorInterface')
            );

            if($stackedContainer = $this->createStackedContainer()){
                $dualContainer->setStackedContainer($stackedContainer);
            }

            if($fallbackContainer = $this->createFallbackContainer()){
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
            return $this->getIlluminateGuard()->getProvider();

        });

        $this->app->singleton('Permit\User\ProviderInterface', function($app){
            return $this->getIlluminateGuard()->getProvider();
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
            $repo = $app->make($class, ['throttleModel' => $app->make($this->throttleModelClass)]);
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

        $this->registerCredentialsValidator();

        $interface = 'Permit\Authentication\AuthenticatorInterface';

        $this->app->singleton($interface, function($app){
            return $this->createAuthenticator();
        });

    }

    protected function createAuthenticator()
    {

        $authenticator = new Authenticator(
            $this->app['Permit\Authentication\UserProviderInterface'],
            $this->app['Permit\Authentication\CredentialsValidatorInterface'],
            $this->app['Permit\CurrentUser\ContainerInterface']
        );

        $loginLogger = $this->app->make(
            'Permit\Support\Laravel\Authentication\UserModelLastLoginWriter'
        );

        $authenticator->whenLoggedIn($loginLogger);

        if ($this->useRegistrar) {
            $authenticator->whenAttempted(function () {
                $object = $this->app->make('Permit\Registration\ChecksActivationOnLogin');
                $args = func_get_args();
                return $object->checkActivation(...$args);
            });
        }

        if ($this->useThrottling) {
            $class = 'Permit\Throttle\ChecksThrottleOnLogin';
            $authenticator->whenLoggedIn(function () use ($class) {
                $object = $this->app->make($class);
                $args = func_get_args();
                return $object->recordSucceedLogin(...$args);
            });
            $authenticator->whenCredentialsInvalid(function () use ($class) {
                $object = $this->app->make($class);
                $args = func_get_args();
                return $object->recordFailedAttempt(...$args);
            });
            $authenticator->whenAttempted(function () use ($class) {
                $object = $this->app->make($class);
                $args = func_get_args();
                return $object->check(...$args);
            });
        }

        if ($this->useDoorKeeper) {
            $authenticator->whenAttempted(function () {
                $object = $this->app->make('Permit\Doorkeeper\ChecksBanOnLogin');
                $args = func_get_args();
                return $object->check(...$args);
            });
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
            $this->registerRegistrarObject();
        }

    }

    protected function registerUserRepository(){

        $this->app->singleton('Permit\Registration\UserRepositoryInterface', function($app){
            return $app->make('Permit\Authentication\UserProviderInterface');

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

            // Some legacy events. @depracted
            $registrar->onBefore('register', function () {
                $this->app->make('events')->fire('auth.registered', func_get_args());
            });

            $registrar->onAfter('register', function () {
                $this->app->make('events')->fire('auth.activation-reserved', func_get_args());
            });

            return $registrar;

        });

    }

    protected function createStackedContainer(){

//         if ($this->app->resolved('Permit\Authentication\UserProviderInterface')) {

            $userProvider = $this->app->make('Permit\Authentication\UserProviderInterface');

//         } else {

//             $class = 'Permit\Support\Laravel\Authentication\MiniUserProvider';
//             $userProvider = $this->app->make($class,[
//                 $this->getIlluminateGuard()->getProvider()
//             ]);

//         }


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

        $this->app->singleton('auth', function($app) use ($useRegistrar)
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
            $groupModel = $userProvider->createModel()->getGroupModelClass();

            $class = 'Permit\Support\Laravel\Groups\EloquentRepository';
            return $app->make($class, ['groupModel' => $app->make($groupModel)]);
        });
    }

    protected function registerGroupManager()
    {
        $interface = 'Permit\Groups\ManagerInterface';
        $this->app->singleton($interface, function($app) {
            return $app->make('Permit\Groups\Manager');
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
