<?php 

use Mockery as m;

use Illuminate\Database\Eloquent\Model;
use Permit\Support\Laravel\User\EloquentUser;

use Permit\Support\Laravel\User\EloquentUserProvider;
use Permit\Hashing\HasherInterface as Hasher;
use Permit\Hashing\NativeHasher;

class EloquentUserProviderTest extends PHPUnit_Framework_TestCase
{

    public function testImplementsInterfaces()
    {

        $provider = $this->newProvider();
        $this->assertInstanceOf('Illuminate\Contracts\Auth\UserProvider', $provider);
        $this->assertInstanceOf('Permit\Authentication\UserProviderInterface', $provider);
        $this->assertInstanceOf('Permit\Registration\UserRepositoryInterface', $provider);
        $this->assertInstanceOf('Permit\User\ProviderInterface', $provider);

    }

    public function testCreateFillsWithoutPasswordAttributeAndSetsHashedDirectly()
    {

        $userModel = $this->mockUser();
        $hasher = $this->mockHasher();
        $provider = $this->newProvider($hasher, $userModel);

        $attributes = [
            'login'=>'hans',
            'hobby'=>'fishing',
            'password' => 'password123'
        ];

        $withoutPassword = $attributes;
        unset($withoutPassword['password']);
        $hashedPassword = md5($attributes['password']);

        $user = $this->mockUser();

        $userModel->shouldReceive('newInstance')->andReturn($user);
        $user->shouldReceive('fill')
             ->with($withoutPassword)
             ->once();

        $hasher->shouldReceive('hash')->andReturn($hashedPassword);

        $user->shouldReceive('setAttribute')
             ->with('password', $hashedPassword);

        $user->shouldReceive('markAsActivated');

        $user->shouldReceive('save')->once();

        $this->assertSame($user, $provider->create($attributes, false));

    }

    public function testCreateOmitsActivationIfPassed()
    {

        $userModel = $this->mockUser();
        $hasher = $this->mockHasher();
        $provider = $this->newProvider($hasher, $userModel);

        $attributes = [
            'login'=>'hans',
            'hobby'=>'fishing',
            'password' => 'password123'
        ];

        $withoutPassword = $attributes;
        unset($withoutPassword['password']);
        $hashedPassword = md5($attributes['password']);

        $user = $this->mockUser();

        $userModel->shouldReceive('newInstance')->andReturn($user);
        $user->shouldReceive('fill')
             ->with($withoutPassword)
             ->once();

        $hasher->shouldReceive('hash')->andReturn($hashedPassword);

        $user->shouldReceive('setAttribute')
             ->with('password', $hashedPassword);

        $user->shouldReceive('markAsActivated')->never();

        $user->shouldReceive('save')->once();

        $this->assertSame($user, $provider->create($attributes, false));

    }

    public function testCreatePerformsActivationIfPassed()
    {

        $userModel = $this->mockUser();
        $hasher = $this->mockHasher();
        $provider = $this->newProvider($hasher, $userModel);

        $attributes = [
            'login'=>'hans',
            'hobby'=>'fishing',
            'password' => 'password123'
        ];

        $withoutPassword = $attributes;
        unset($withoutPassword['password']);
        $hashedPassword = md5($attributes['password']);

        $user = $this->mockUser();

        $userModel->shouldReceive('newInstance')->andReturn($user);
        $user->shouldReceive('fill')
             ->with($withoutPassword)
             ->once();

        $hasher->shouldReceive('hash')->andReturn($hashedPassword);

        $user->shouldReceive('setAttribute')
             ->with('password', $hashedPassword);

        $user->shouldReceive('markAsActivated')->once();

        $user->shouldReceive('save')->once();

        $this->assertSame($user, $provider->create($attributes, true));

    }

    public function testSaveUpdatesPasswordIfChanged()
    {

        $hasher = $this->mockHasher();
        $provider = $this->newProvider($hasher);
        $password = 'test123';

        $hashedPassword = md5($password);

        $user = $this->mockUser();

        $hasher->shouldReceive('hash')->andReturn($hashedPassword);

        $user->shouldReceive('getAttribute')
             ->with('password')
             ->andReturn($password);

        $user->shouldReceive('isDirty')
             ->with('password')
             ->andReturn(true);

        $user->shouldReceive('setAttribute')
             ->with('password', $hashedPassword);

        $user->shouldReceive('save')->once()->andReturn(true);

        $this->assertTrue($provider->save($user));

    }

    public function testSaveDoesNotUpdatePasswordIfNotChanged()
    {

        $hasher = $this->mockHasher();
        $provider = $this->newProvider($hasher);
        $password = 'test123';

        $hashedPassword = md5($password);

        $user = $this->mockUser();

        $hasher->shouldReceive('hash')->andReturn($hashedPassword);

        $user->shouldReceive('getAttribute')
             ->with('password')
             ->never();

        $user->shouldReceive('isDirty')
             ->with('password')
             ->andReturn(false);

        $user->shouldReceive('setAttribute')
             ->with('password', $hashedPassword)
             ->never();

        $user->shouldReceive('save')->once()->andReturn(true);

        $this->assertTrue($provider->save($user));

    }

    public function tearDown()
    {
        m::close();
    }

    protected function newProvider(Hasher $hasher=null, Model $user=null, $tokenRepo=null)
    {
        $tokenRepo = $tokenRepo ?: m::mock('Permit\Token\RepositoryInterface');
        $hasher = $hasher ?: $this->newHasher();
        $user = $user ?: $this->newUser();
        return new EloquentUserProvider($user, $hasher, $tokenRepo);
    }

    protected function newUser($id=1)
    {
        $user = new EloquentUser;
        $user->id = $id;
        return $user;
    }

    protected function newHasher()
    {
        return new NativeHasher;
    }

    protected function mockUser()
    {
        return m::mock('Permit\Support\Laravel\User\EloquentUser');
    }

    protected function mockHasher()
    {
        return m::mock('Permit\Hashing\HasherInterface');
    }


}