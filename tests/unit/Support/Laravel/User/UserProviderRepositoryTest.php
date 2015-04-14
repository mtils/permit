<?php 

use Mockery as m;

use Permit\Support\Laravel\User\UserProviderRepository;

class UserProviderRepositoryTest extends PHPUnit_Framework_TestCase{

    protected $interface = 'Illuminate\Auth\UserProviderInterface';

    public function testImplementsInterface()
    {
        $provider = m::mock($this->interface);
        $repo = new UserProviderRepository($provider);

        $this->assertInstanceOf(
            'Permit\Authentication\UserProviderInterface',
            $repo
        );

    }

    public function testFindByCredentialsForwardsToProvider()
    {
        $provider = m::mock($this->interface);
        $repo = new UserProviderRepository($provider);
        $credentials = ['login' => 'michal76', 'password'=>'123'];

        $provider->shouldReceive('retrieveByCredentials')
                 ->with($credentials)
                 ->once()
                 ->andReturn('return');

        $this->assertEquals('return', $repo->findByCredentials($credentials));
    }

    public function tearDown()
    {
        m::close();
    }

}