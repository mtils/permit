<?php 

use Mockery as m;

use Permit\Support\Laravel\CurrentUser\GuardContainer;
use Permit\User\GenericUser;
use PHPUnit\Framework\TestCase;

class GuardContainerTest extends TestCase
{

    public function testImplementsInterfaces()
    {
        $container = $this->newContainer();

        $this->assertInstanceOf(
            'Permit\CurrentUser\ContainerInterface',
            $container
        );

        $this->assertInstanceOf(
            'Permit\CurrentUser\CanRememberUser',
            $container
        );
    }

    public function testUserForwardsToGuard(){

        $guard = $this->mockGuard();
        $container = $this->newContainer($guard);
        $user = $this->newUser();

        $guard->shouldReceive('user')
              ->once()
              ->andReturn($user);

        $this->assertSame($user, $container->user());
    }

    public function testSetUserForwardsToGuardIfNotPersisting()
    {
        $guard = $this->mockGuard();
        $container = $this->newContainer($guard);
        $user = $this->mockUser();

        $guard->shouldReceive('setUser')
              ->with($user)
              ->once();

        $this->assertSame($user, $container->setUser($user, false));
    }

    public function testSetUserForwardsToLoginIfPersisting()
    {
        $guard = $this->mockGuard();
        $container = $this->newContainer($guard);
        $user = $this->mockUser();

        $guard->shouldReceive('login')
              ->with($user, false)
              ->once();

        $this->assertSame($user, $container->setUser($user, true));
    }

    public function testSetAndRememberForwardsToLogin()
    {
        $guard = $this->mockGuard();
        $container = $this->newContainer($guard);
        $user = $this->mockUser();

        $guard->shouldReceive('login')
              ->with($user, true)
              ->once();

        $this->assertSame($user, $container->setAndRemember($user));
    }

    public function testClearUserForwardsToGuard()
    {
        $guard = $this->mockGuard();
        $container = $this->newContainer($guard);
        $user = $this->mockUser();

        $guard->shouldReceive('logout')
              ->once();

        $this->assertTrue($container->clearUser($user));
    }

    public function newContainer($guard=null)
    {
        $guard = $guard ?: $this->mockGuard();
        return new GuardContainer($guard);
    }

    public function mockGuard()
    {
        return m::mock(Illuminate\Contracts\Auth\StatefulGuard::class);
    }

    public function newUser($id=1)
    {
        $user = new GenericUser;
        $user->setAuthId($id);
        return $user;
    }

    public function mockUser()
    {
        return m::mock(
            'Permit\User\UserInterface',
            'Illuminate\Contracts\Auth\Authenticatable'
        );

    }

    public function tearDown()
    {
        m::close();
    }

}
