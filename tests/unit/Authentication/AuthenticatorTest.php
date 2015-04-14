<?php 

use Mockery as m;

use Permit\Authentication\Authenticator;
use Permit\User\GenericUser;
use Permit\Authentication\Exception\CredentialsNotFoundException;
use Permit\Authentication\Exception\CredentialsInvalidException;

class AuthenticatorTest extends PHPUnit_Framework_TestCase{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Authentication\AuthenticatorInterface',
            $this->newAuthenticator()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testAuthenticateAndRememberThrowsExceptionIfContainerDoesntSupportIt()
    {
        $authenticator = $this->newAuthenticator();
        $authenticator->authenticate([], true);
    }

    /**
     * @expectedException \Permit\Authentication\Exception\CredentialsNotFoundException
     **/
    public function testAuthenticateThrowsExceptionIfUserNotFound()
    {
        $userProvider = $this->mockUserProvider();
        $authenticator = $this->newAuthenticator($userProvider);

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn(null)
                     ->once();
        $authenticator->authenticate([], false);
    }

    /**
     * @expectedException \Permit\Authentication\Exception\CredentialsInvalidException
     **/
    public function testAuthenticateThrowsExceptionIfCredentialsNotValid()
    {
        $userProvider = $this->mockUserProvider();
        $validator = $this->mockCredentialsValidator();
        $authenticator = $this->newAuthenticator($userProvider, $validator);
        $user = $this->newUser();

        $credentials = ['username'=>'foo', 'password'=>'passwort123'];

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn($user)
                     ->once();

        $validator->shouldReceive('validateCredentials')
                  ->with($user, $credentials)
                  ->once()
                  ->andReturn(false);

        $authenticator->authenticate($credentials, false);

    }

    public function testAuthenticatePutsUserIntoContainerIfLoginSucceeded()
    {
        $userProvider = $this->mockUserProvider();
        $validator = $this->mockCredentialsValidator();
        $container = $this->mockUserContainer();
        $authenticator = $this->newAuthenticator(
            $userProvider,
            $validator,
            $container
        );
        $user = $this->newUser();

        $credentials = ['username'=>'foo', 'password'=>'passwort123'];

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn($user)
                     ->once();

        $validator->shouldReceive('validateCredentials')
                  ->with($user, $credentials)
                  ->once()
                  ->andReturn(true);

        $container->shouldReceive('setUser')
                  ->with($user)
                  ->once()
                  ->andReturn($user);

        $authenticator->authenticate($credentials, false);

    }

    public function testAuthenticatePutsUserIntoRememberContainerIfRememberPassed()
    {
        $userProvider = $this->mockUserProvider();
        $validator = $this->mockCredentialsValidator();
        $container = $this->mockRememberContainer();
        $authenticator = $this->newAuthenticator(
            $userProvider,
            $validator,
            $container
        );
        $user = $this->newUser();

        $credentials = ['username'=>'foo', 'password'=>'passwort123'];

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn($user)
                     ->once();

        $validator->shouldReceive('validateCredentials')
                  ->with($user, $credentials)
                  ->once()
                  ->andReturn(true);

        $container->shouldReceive('setAndRemember')
                  ->with($user)
                  ->once()
                  ->andReturn($user);

        $authenticator->authenticate($credentials, true);

    }

    public function testAuthenticateFiresAllDesiredEvents()
    {
        $userProvider = $this->mockUserProvider();
        $validator = $this->mockCredentialsValidator();
        $container = $this->mockRememberContainer();
        $eventBus = $this->mockEventBus();

        $authenticator = $this->newAuthenticator(
            $userProvider,
            $validator,
            $container
        );
        $authenticator->setEventBus($eventBus);

        $user = $this->newUser();

        $credentials = ['username'=>'foo', 'password'=>'passwort123'];
        $remember = true;

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn($user)
                     ->once();

        $validator->shouldReceive('validateCredentials')
                  ->with($user, $credentials)
                  ->once()
                  ->andReturn(true);

        $container->shouldReceive('setAndRemember')
                  ->with($user)
                  ->once()
                  ->andReturn($user);

        $eventBus->shouldReceive('fire')
                 ->with(
                    $authenticator->preAttemptEvent,
                    [$credentials, $remember],
                    false
                 )
                 ->once();

        $eventBus->shouldReceive('fire')
                 ->with(
                    $authenticator->postAttemptEvent,
                    [$user, $credentials, $remember],
                    false
                 )
                 ->once();

        $eventBus->shouldReceive('fire')
                 ->with(
                    $authenticator->loggedInEvent,
                    [$user, $remember],
                    false
                 )
                 ->once();

        $authenticator->authenticate($credentials, $remember);

    }

    public function testAuthenticateFiresEventIfCredentialsNotFound()
    {
        $userProvider = $this->mockUserProvider();
        $bus = $this->mockEventBus();
        $validator = $this->mockCredentialsValidator();
        $authenticator = $this->newAuthenticator($userProvider, $validator);
        $authenticator->setEventBus($bus);

        $credentials = ['username'=>'foo', 'password'=>'passwort123'];
        $remember = false;

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn(false)
                     ->once();

        $bus->shouldReceive('fire')
            ->with(
                $authenticator->preAttemptEvent,
                [$credentials, $remember],
                false
            )
            ->once();

        $bus->shouldReceive('fire')
            ->with(
                $authenticator->credentialsNotFoundEvent,
                [$credentials, $remember],
                false
            )
            ->once();

        try{
            $authenticator->authenticate($credentials, $remember);
        }
        catch(CredentialsNotFoundException $e){}

    }

    public function testAuthenticateFiresEventIfCredentialsInvalid()
    {
        $userProvider = $this->mockUserProvider();
        $bus = $this->mockEventBus();
        $validator = $this->mockCredentialsValidator();
        $authenticator = $this->newAuthenticator($userProvider, $validator);
        $authenticator->setEventBus($bus);
        $user = $this->newUser();

        $credentials = ['username'=>'foo', 'password'=>'passwort123'];
        $remember = false;

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn($user)
                     ->once();

        $validator->shouldReceive('validateCredentials')
                  ->with($user, $credentials)
                  ->once()
                  ->andReturn(false);

        $bus->shouldReceive('fire')
            ->with(
                $authenticator->preAttemptEvent,
                [$credentials, $remember],
                false
            )
            ->once();

        $bus->shouldReceive('fire')
            ->with(
                $authenticator->credentialsInvalidEvent,
                [$user, $credentials, $remember],
                false
            )
            ->once();

        try{
            $authenticator->authenticate($credentials, $remember);
        }
        catch(CredentialsInvalidException $e){}

    }

    public function testLogoutRemovesUseFromContainerAndReturnsIt()
    {

        $container = $this->mockUserContainer();
        $authenticator = $this->newAuthenticator(null,null,$container);
        $user = $this->newUser();

        $container->shouldReceive('user')
                  ->once()
                  ->andReturn($user);

        $container->shouldReceive('clearUser')
                  ->once();

        $this->assertSame($user, $authenticator->logout());
    }

    public function testLogoutFiresDesiredEvents()
    {

        $container = $this->mockUserContainer();
        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator(null,null,$container);
        $authenticator->setEventBus($eventBus);
        $user = $this->newUser();

        $container->shouldReceive('user')
                  ->once()
                  ->andReturn($user);

        $container->shouldReceive('clearUser')
                  ->once();

        $eventBus->shouldReceive('fire')
                 ->with($authenticator->preLogoutEvent, $user, false)
                 ->once();

        $eventBus->shouldReceive('fire')
                 ->with($authenticator->postLogoutEvent, $user, false)
                 ->once();

        $this->assertSame($user, $authenticator->logout());
    }

    public function testWhenAttemptingPassesListenerToEventBus()
    {

        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator();
        $authenticator->setEventBus($eventBus);
        $callable = 'strpos';


        $eventBus->shouldReceive('listen')
                 ->with($authenticator->preAttemptEvent, $callable, 0)
                 ->once();

        $this->assertNull($authenticator->whenAttempting($callable));
    }

    public function testWhenCredentialsNoFoundPassesListenerToEventBus()
    {

        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator();
        $authenticator->setEventBus($eventBus);
        $callable = 'strpos';


        $eventBus->shouldReceive('listen')
                 ->with($authenticator->credentialsNotFoundEvent, $callable, 0)
                 ->once();

        $this->assertNull($authenticator->whenCredentialsNotFound($callable));
    }

    public function testWhenCredentialsInvalidPassesListenerToEventBus()
    {

        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator();
        $authenticator->setEventBus($eventBus);
        $callable = 'strpos';


        $eventBus->shouldReceive('listen')
                 ->with($authenticator->credentialsInvalidEvent, $callable, 0)
                 ->once();

        $this->assertNull($authenticator->whenCredentialsInvalid($callable));
    }

    public function testWhenAttemptedPassesListenerToEventBus()
    {

        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator();
        $authenticator->setEventBus($eventBus);
        $callable = 'strpos';


        $eventBus->shouldReceive('listen')
                 ->with($authenticator->postAttemptEvent, $callable, 0)
                 ->once();

        $this->assertNull($authenticator->whenAttempted($callable));
    }

    public function testWhenLoggedInPassesListenerToEventBus()
    {

        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator();
        $authenticator->setEventBus($eventBus);
        $callable = 'strpos';


        $eventBus->shouldReceive('listen')
                 ->with($authenticator->loggedInEvent, $callable, 0)
                 ->once();

        $this->assertNull($authenticator->whenLoggedIn($callable));
    }

    public function testWhenLoggingOutPassesListenerToEventBus()
    {

        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator();
        $authenticator->setEventBus($eventBus);
        $callable = 'strpos';


        $eventBus->shouldReceive('listen')
                 ->with($authenticator->preLogoutEvent, $callable, 0)
                 ->once();

        $this->assertNull($authenticator->whenLoggingOut($callable));
    }

    public function testWhenLoggedOutPassesListenerToEventBus()
    {

        $eventBus = $this->mockEventBus();
        $authenticator = $this->newAuthenticator();
        $authenticator->setEventBus($eventBus);
        $callable = 'strpos';


        $eventBus->shouldReceive('listen')
                 ->with($authenticator->postLogoutEvent, $callable, 0)
                 ->once();

        $this->assertNull($authenticator->whenLoggedOut($callable));
    }

    public function newAuthenticator($userProvider=null,
                                     $credentialsValidator=null,
                                     $userContainer=null)
    {
        $userProvider = $userProvider ?: $this->mockUserProvider();
        $credentialsValidator = $credentialsValidator ?: $this->mockCredentialsValidator();
        $userContainer = $userContainer ?: $this->mockUserContainer();
        return new Authenticator(
            $userProvider,
            $credentialsValidator,
            $userContainer
        );
    }

    public function mockUserProvider()
    {
        return m::mock('Permit\Authentication\UserProviderInterface');
    }

    public function mockCredentialsValidator()
    {
        return m::mock('Permit\Authentication\CredentialsValidatorInterface');
    }

    public function mockUserContainer()
    {
        return m::mock('Permit\CurrentUser\ContainerInterface');
    }

    public function mockRememberContainer()
    {
        return m::mock(
            'Permit\CurrentUser\ContainerInterface',
            'Permit\CurrentUser\CanRememberUser'
        );
    }

    public function mockEventBus()
    {
        return m::mock('Signal\Contracts\NamedEvent\Bus');
    }

    public function newUser($id=1)
    {
        $user = new GenericUser;
        $user->id = $id;
        return $user;
    }

    public function tearDown()
    {
        m::close();
    }

}