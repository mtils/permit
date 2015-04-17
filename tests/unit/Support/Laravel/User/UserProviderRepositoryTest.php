<?php 

use Mockery as m;

use Permit\Support\Laravel\User\UserProviderRepository;
use Permit\Registration\Activation\DriverInterface;
use Permit\Hashing\HasherInterface;

use Illuminate\Auth\UserProviderInterface;

class UserProviderRepositoryTest extends PHPUnit_Framework_TestCase{

    protected $interface = 'Illuminate\Auth\UserProviderInterface';

    public function testImplementsInterface()
    {
        $provider = m::mock($this->interface);
        $repo = $this->newRepository($provider);

        $this->assertInstanceOf(
            'Permit\Authentication\UserProviderInterface',
            $repo
        );

    }

    public function testFindByCredentialsForwardsToProvider()
    {
        $provider = m::mock($this->interface);
        $repo = $this->newRepository($provider);
        $credentials = ['login' => 'michal76', 'password'=>'123'];

        $provider->shouldReceive('retrieveByCredentials')
                 ->with($credentials)
                 ->once()
                 ->andReturn('return');

        $this->assertEquals('return', $repo->findByCredentials($credentials));
    }

    protected function newRepository(UserProviderInterface $provider=null,
                                     HasherInterface $hasher=null,
                                     DriverInterface $driver=null)
    {
        $provider = $provider ?: $this->mockProvider();
        $hasher = $hasher ?: $this->mockHasher();
        $activator = $driver ?: $this->mockActivator();
        return new UserProviderRepository(
            $provider, $activator, $hasher
        );
    }

    protected function mockProvider()
    {
        return m::mock($this->interface);
    }

    protected function mockHasher()
    {
        return m::mock('Permit\Hashing\HasherInterface');
    }

    protected function mockActivator()
    {
        return m::mock('Permit\Registration\Activation\DriverInterface');
    }

    public function tearDown()
    {
        m::close();
    }

}