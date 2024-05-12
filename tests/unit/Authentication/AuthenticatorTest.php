<?php

use Mockery as m;

use Permit\Authentication\Authenticator;
use Permit\User\GenericUser;
use Permit\Authentication\Exception\CredentialsNotFoundException;
use Permit\Authentication\Exception\CredentialsInvalidException;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Authentication\AuthenticatorInterface',
            $this->newAuthenticator()
        );
    }

    public function testAuthenticateAndRememberThrowsExceptionIfContainerDoesntSupportIt()
    {
        $this->expectException(\InvalidArgumentException::class);
        $authenticator = $this->newAuthenticator();
        $authenticator->authenticate([], true);
    }

    public function testAuthenticateThrowsExceptionIfUserNotFound()
    {
        $this->expectException(
            \Permit\Authentication\Exception\CredentialsNotFoundException::class
        );
        $userProvider = $this->mockUserProvider();
        $authenticator = $this->newAuthenticator($userProvider);

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn(null)
                     ->once();
        $authenticator->authenticate([], false);
    }

    public function testAuthenticateThrowsExceptionIfCredentialsNotValid()
    {
        $this->expectException(
            \Permit\Authentication\Exception\CredentialsInvalidException::class
        );
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

        $authenticator = $this->newAuthenticator(
            $userProvider,
            $validator,
            $container
        );

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

        $authenticator->authenticate($credentials, $remember);

    }

    public function testAuthenticateFiresEventIfCredentialsNotFound()
    {
        $userProvider = $this->mockUserProvider();
        $validator = $this->mockCredentialsValidator();
        $authenticator = $this->newAuthenticator($userProvider, $validator);

        $credentials = ['username'=>'foo', 'password'=>'passwort123'];
        $remember = false;

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn(false)
                     ->once();


        try{
            $authenticator->authenticate($credentials, $remember);
        }
        catch(CredentialsNotFoundException $e){}

    }

    public function testAuthenticateFiresEventIfCredentialsInvalid()
    {
        $userProvider = $this->mockUserProvider();
        $validator = $this->mockCredentialsValidator();
        $authenticator = $this->newAuthenticator($userProvider, $validator);

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

        $authenticator = $this->newAuthenticator(null,null,$container);

        $user = $this->newUser();

        $container->shouldReceive('user')
                  ->once()
                  ->andReturn($user);

        $container->shouldReceive('clearUser')
                  ->once();

        $this->assertSame($user, $authenticator->logout());
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

    public function newUser($id=1)
    {
        $user = new GenericUser;
        $user->id = $id;
        return $user;
    }

    public function tearDown(): void
    {
        m::close();
    }

}
