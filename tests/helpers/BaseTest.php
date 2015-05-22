<?php 

use Mockery as m;

use Permit\Registration\GenericUser;
use Permit\Support\Laravel\User\EloquentUser;
use Permit\Hashing\NativeHasher;
use Permit\Random\StrShuffleGenerator;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{

    public function newUser($id=1)
    {
        $user = new GenericUser;
        $user->id = $id;
        return $user;
    }

    protected function mockUser()
    {
        return m::mock('Permit\Registration\ActivatableInterface');
    }

    protected function newEloquentUser($id=1)
    {
        $user = new EloquentUser;
        $user->id = $id;
        return $user;
    }

    protected function mockEloquentUser()
    {
        return m::mock('Permit\Support\Laravel\User\EloquentUser');
    }

    protected function newHasher()
    {
        return new NativeHasher();
    }

    protected function mockHasher()
    {
        return m::mock('Permit\Hashing\HasherInterface');
    }

    protected function newRandomGenerator()
    {
        return new StrShuffleGenerator;
    }

    protected function mockRandomGenerator()
    {
        return m::mock('Permit\Random\GeneratorInterface');
    }

    protected function mockUserProvider()
    {
        return m::mock('Permit\Authentication\UserProviderInterface');
    }

    protected function mockTokens()
    {
        return m::mock('Permit\Token\RepositoryInterface');
    }

    protected function mockUsers()
    {
        return m::mock('Permit\Registration\UserRepositoryInterface');
    }

    public function tearDown()
    {
        m::close();
    }

}