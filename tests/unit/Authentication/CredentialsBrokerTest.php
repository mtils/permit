<?php 

use Mockery as m;
use Permit\Authentication\CredentialsBroker;
use Permit\Token\RepositoryInterface as Tokens;

class CredentialsBrokerTest extends PHPUnit_Framework_TestCase{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Authentication\CredentialsBrokerInterface',
            $this->newBroker()
        );
    }

    /**
     * @expectedException \Permit\User\UserNotFoundException
     **/
    public function testReserveResetThrowsExceptionIfUserNotFound()
    {
        $userProvider = $this->mockUserProvider();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker($userProvider, $tokens);

        $credentials = ['user'=>'michael'];

        $userProvider->shouldReceive('findByCredentials')
                     ->with($credentials)
                     ->andReturn(null);

        $broker->reserveReset($credentials);

    }

    public function testReserveResetCreatesValidToken()
    {

        $userProvider = $this->mockUserProvider();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker($userProvider, $tokens);

        $credentials = ['user'=>'michael'];
        $user = $this->mockUser();
        $now = new DateTime('2015-01-05 10:00:00');
        $expires = 60;
        $token = 'abc';

        $expiresAt = clone $now;
        $expiresAt->modify("+$expires minutes");

        $broker->setNow($now);
        $broker->setExpiryMinutes($expires);
        $broker->calculateExpiryWith(function($date, $minutes) use ($expiresAt, $now, $expires){
            $this->assertEquals($now, $date);
            $this->assertEquals($expires, $minutes);
            return $expiresAt;
        });

        $userProvider->shouldReceive('findByCredentials')
                     ->with($credentials)
                     ->andReturn($user);

        $tokens->shouldReceive('create')
               ->with($user, Tokens::PASSWORD_RESET, $expiresAt)
               ->andReturn($token);

        $this->assertEquals($token, $broker->reserveReset($credentials));

    }

    public function testReserveResetCallsCallableWithUserAndToken()
    {

        $userProvider = $this->mockUserProvider();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker($userProvider, $tokens);

        $credentials = ['user'=>'michael'];
        $user = $this->mockUser();
        $token = 'abc';

        $userProvider->shouldReceive('findByCredentials')
                     ->andReturn($user);

        $tokens->shouldReceive('create')
               ->andReturn($token);

        $callable = function($param1, $param2) use ($user, $token){
            $this->assertSame($user, $param1);
            $this->assertEquals($token, $param2);
        };

        $this->assertEquals($token, $broker->reserveReset($credentials, $callable));

    }

    /**
     * @expectedException \Permit\Token\TokenMissingException
     **/
    public function testResetThrowsExceptionOfNoTokenPassed()
    {
        $broker = $this->newBroker();
        $broker->reset([]);
    }

    /**
     * @expectedException \Permit\User\UserNotFoundException
     **/
    public function testResetThrowsExceptionIfUserNotFound()
    {

        $users = $this->mockUsers();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker(null, $tokens, $users);

        $token = 'abc';
        $credentials = ['user'=>'michael',$broker->tokenKey=>$token];
        $authId = 44;

        $tokens->shouldReceive('getAuthIdByToken')
               ->with($token, Tokens::PASSWORD_RESET)
               ->andReturn($authId);

        $users->shouldReceive('retrieveByAuthId')
              ->with($authId)
              ->andReturn(null);

        $broker->reset($credentials);

    }

    public function testResetDeletesTokenAndReturnsUser()
    {

        $users = $this->mockUsers();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker(null, $tokens, $users);

        $user = $this->mockUser();
        $token = 'abc';
        $credentials = ['user'=>'michael',$broker->tokenKey=>$token];
        $filteredCredentials = ['user'=>'michael'];
        $authId = 44;

        $tokens->shouldReceive('getAuthIdByToken')
               ->with($token, Tokens::PASSWORD_RESET)
               ->andReturn($authId);

        $tokens->shouldReceive('invalidate')
               ->with($user, Tokens::PASSWORD_RESET, $token)
               ->once();

        $users->shouldReceive('retrieveByAuthId')
              ->with($authId)
              ->andReturn($user);

        $users->shouldReceive('save')
              ->with($user)
              ->andReturn(true);

        $broker->setCredentialsBy(function($param1, $param2) use ($user, $filteredCredentials){
            $this->assertSame($user, $param1);
            $this->assertEquals($filteredCredentials, $param2);
        });

        $broker->reset($credentials);

    }

    public function testExpiresAtReturnsNullIfNoExpiresSetted()
    {
        $broker = $this->newBroker();
        $broker->setExpiryMinutes(0);

        $this->assertNull($broker->getExpiresAt());
    }

    protected function newBroker($userProvider=null, $tokenRepository=null,
                                 $userRepository=null)
    {
        $userProvider = $userProvider ?: $this->mockUserProvider();
        $tokenRepository = $tokenRepository ?: $this->mockTokens();
        $userRepository = $userRepository ?: $this->mockUsers();
        return new CredentialsBroker($userProvider, $tokenRepository, $userRepository);
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

    protected function mockUser()
    {
        return m::mock('Permit\Registration\ActivatableInterface');
    }

}