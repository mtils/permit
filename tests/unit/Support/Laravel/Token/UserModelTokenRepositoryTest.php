<?php 

use Mockery as m;

use Illuminate\Database\Eloquent\Model;

use Permit\Token\RepositoryInterface as Tokens;
use Permit\Support\Laravel\Token\UserModelTokenRepository;
use Permit\Random\GeneratorInterface as RandomGenerator;

class UserModelTokenRepositoryTest extends BaseTest
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Token\RepositoryInterface',
            $this->newRepository()
        );
    }

    public function testGetReturnsUserSettedProperty()
    {

        $repo = $this->newRepository();
        $user = $this->mockEloquentUser();

        $repo->rememberKey = 'remember_key';
        $repo->activationKey = 'activation_key';
        $repo->passwordResetKey = 'password_reset_key';
        $repo->oauthKey = 'oauth_key';

        $user->shouldReceive('getAttribute')
             ->with($repo->rememberKey)
             ->once()
             ->andReturn('remember');

        $user->shouldReceive('getAttribute')
             ->with($repo->activationKey)
             ->once()
             ->andReturn('activation');

        $user->shouldReceive('getAttribute')
             ->with($repo->passwordResetKey)
             ->once()
             ->andReturn('password');

        $user->shouldReceive('getAttribute')
             ->with($repo->oauthKey)
             ->once()
             ->andReturn('oauth');


        $this->assertEquals('remember', $repo->get($user, Tokens::REMEMBER));
        $this->assertEquals('activation', $repo->get($user, Tokens::ACTIVATION));
        $this->assertEquals('password', $repo->get($user, Tokens::PASSWORD_RESET));
        $this->assertEquals('oauth', $repo->get($user, Tokens::OAUTH));

    }

    public function testGetAuthIdReturnsUsersAuthId()
    {

        $userModel = $this->mockEloquentUser();
        $repo = $this->newRepository($userModel);
        $user = $this->mockEloquentUser();

        $token = $this->newRandomGenerator()->generate(60);

        $repo->rememberKey = 'remember_key';
        $repo->activationKey = 'activation_key';
        $repo->passwordResetKey = 'password_reset_key';
        $repo->oauthKey = 'oauth_key';

        $userModel->shouldReceive('where')
                  ->with($repo->rememberKey, $token)
                  ->andReturn($userModel);

        $userModel->shouldReceive('first')->once()->andReturn($user);

        $user->shouldReceive('getAuthId')
             ->andReturn(255);

        $this->assertEquals(255, $repo->getAuthIdByToken($token, Tokens::REMEMBER));
    }

    /**
     * @expectedException \Permit\Token\TokenInvalidException
     **/
    public function testGetAuthIdThrowsExceptionIfTokenIsInvalid()
    {

        $userModel = $this->mockEloquentUser();
        $repo = $this->newRepository($userModel);
        $repo->setTokenLength(60);
        $user = $this->mockEloquentUser();

        $token = $this->newRandomGenerator()->generate(20);

        $repo->rememberKey = 'remember_key';
        $repo->activationKey = 'activation_key';
        $repo->passwordResetKey = 'password_reset_key';
        $repo->oauthKey = 'oauth_key';

        $repo->getAuthIdByToken($token, Tokens::REMEMBER);
    }

    /**
     * @expectedException \Permit\Token\TokenNotFoundException
     **/
    public function testGetAuthIdThrowsExceptionIfTokenNotFound()
    {

        $userModel = $this->mockEloquentUser();
        $repo = $this->newRepository($userModel);
        $user = $this->mockEloquentUser();

        $token = $this->newRandomGenerator()->generate(60);

        $repo->rememberKey = 'remember_key';
        $repo->activationKey = 'activation_key';
        $repo->passwordResetKey = 'password_reset_key';
        $repo->oauthKey = 'oauth_key';

        $userModel->shouldReceive('where')
                  ->with($repo->rememberKey, $token)
                  ->andReturn($userModel);

        $userModel->shouldReceive('first')->once()->andReturn(null);

        $repo->getAuthIdByToken($token, Tokens::REMEMBER);
    }

    public function testSetUsesSettedProperty()
    {

        $randomGenerator = $this->mockRandomGenerator();
        $repo = $this->newRepository(null, $randomGenerator);
        $user = $this->mockEloquentUser();

        $repo->rememberKey = 'remember_key';
        $repo->activationKey = 'activation_key';
        $repo->passwordResetKey = 'password_reset_key';
        $repo->oauthKey = 'oauth_key';

        $token = $this->newRandomGenerator()->generate(60);

        $randomGenerator->shouldReceive('generate')
                        ->andReturn($token);

        $user->shouldReceive('setAttribute')
             ->with($repo->rememberKey, $token)
             ->once();

        $user->shouldReceive('setAttribute')
             ->with($repo->activationKey, $token)
             ->once();

        $user->shouldReceive('setAttribute')
             ->with($repo->passwordResetKey, $token)
             ->once();

        $user->shouldReceive('setAttribute')
             ->with($repo->oauthKey, $token)
             ->once();

        $user->shouldReceive('save')->times(4);


        $this->assertEquals($token, $repo->create($user, Tokens::REMEMBER));
        $this->assertEquals($token, $repo->create($user, Tokens::ACTIVATION));
        $this->assertEquals($token, $repo->create($user, Tokens::PASSWORD_RESET));
        $this->assertEquals($token, $repo->create($user, Tokens::OAUTH));

    }

    public function newRepository(Model $userModel=null,
                                  RandomGenerator $randomGenerator=null)
    {

        $userModel = $userModel ?: $this->newEloquentUser();
        $randomGenerator = $randomGenerator ?: $this->newRandomGenerator();

        return new UserModelTokenRepository($userModel, $randomGenerator);
    }

}