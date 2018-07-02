<?php

use Mockery as m;

use Permit\Support\Laravel\User\EloquentRepository;
use Permit\Support\Laravel\User\EloquentUser;
use PHPUnit\Framework\TestCase;

class EloquentUserRepositoryTest extends TestCase
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Registration\UserRepositoryInterface',
            $this->newRepository()
        );
    }

    public function testRetrieveByAuthIdForwardsToFind()
    {

        $userModel = m::mock('Permit\Support\Laravel\User\EloquentUser');
        $repo = $this->newRepository($userModel);

        $resultUser = $this->newUser(16);

        $userModel->shouldReceive('find')
                  ->with(16)
                  ->once()
                  ->andReturn($resultUser);

        $this->assertSame($resultUser, $repo->retrieveByAuthId(16));

    }

    public function testCreateForwardsToCreate()
    {

        $userModel = m::mock('Permit\Support\Laravel\User\EloquentUser');
        $repo = $this->newRepository($userModel);

        $resultUser = $this->newUser(16);
        $attributes = ['login'=>'michael88','email'=>'foo@bar.de'];

        $userModel->shouldReceive('newInstance')
                  ->with($attributes)
                  ->once()
                  ->andReturn($resultUser);

        $this->assertSame($resultUser, $repo->create($attributes));

    }

    public function testCreateForwardsToSave()
    {

        $repo = $this->newRepository($this->newUser(16));

        $resultUser = $this->newUser(16);
        $savedUser = m::mock('Permit\Support\Laravel\User\EloquentUser');

        $savedUser->shouldReceive('save')
                  ->once()
                  ->andReturn(true);

        $this->assertTrue($repo->save($savedUser));

    }

    public function tearDown()
    {
        m::close();
    }

    public function newRepository($userModel=null)
    {
        $userModel = $userModel ?: $this->newUser();
        return new EloquentRepository($userModel);
    }

    protected function newUser($id=1)
    {
        $user = new EloquentUser;
        $user->id = $id;
        return $user;
    }

}
