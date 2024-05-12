<?php

use Mockery as m;
use Permit\Authentication\CredentialsBroker;
use Permit\Token\RepositoryInterface as Tokens;
use Permit\Registration\GenericUser;

class CredentialsBrokerTest extends BaseTest{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Authentication\CredentialsBrokerInterface',
            $this->newBroker()
        );
    }

    public function testReserveResetThrowsExceptionIfUserNotFound()
    {
        $this->expectException(\Permit\User\UserNotFoundException::class);
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

    public function testResetThrowsExceptionOfNoTokenPassed()
    {
        $this->expectException(\Permit\Token\TokenMissingException::class);
        $broker = $this->newBroker();
        $broker->reset([]);
    }

    public function testResetThrowsExceptionIfUserNotFound()
    {
        $this->expectException(\Permit\User\UserNotFoundException::class);

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

    public function testApplyCredentialsSetsPropertiesBy__set()
    {

        $users = $this->mockUsers();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker(null, $tokens, $users);

        $user = new PropertyOverloadedUser;
        $token = 'abc';

        $credentials = [
            'user'      => 'michael',
            'password'  => '123',
            '_csrf'     => 'cba',
            $broker->tokenKey=>$token
        ];

        $filteredCredentials = [
            'user'      => 'michael',
            'password'  => '123'
        ];

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

        $broker->reset($credentials);

        $this->assertEquals($filteredCredentials, $user->attributes);

    }

    public function testApplyCredentialsSetsPropertiesByArrayAccess()
    {

        $users = $this->mockUsers();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker(null, $tokens, $users);

        $user = new ArrayAccessableUser;
        $token = 'abc';

        $credentials = [
            'user'      => 'michael',
            'password'  => '123',
            'password_confirmation' => '123',
            '_csrf'     => 'cba',
            $broker->tokenKey=>$token
        ];

        $filteredCredentials = [
            'user'      => 'michael',
            'password'  => '123'
        ];

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

        $broker->reset($credentials);

        $this->assertEquals($filteredCredentials, $user->attributes);

    }

    public function testApplyCredentialsThrowsExceptionIfCantSetAttributes()
    {
        $this->expectException(\RuntimeException::class);

        $users = $this->mockUsers();
        $tokens = $this->mockTokens();
        $broker = $this->newBroker(null, $tokens, $users);

        $user = new GenericUser;
        $token = 'abc';

        $credentials = [
            'user'      => 'michael',
            'password'  => '123',
            '_csrf'     => 'cba',
            $broker->tokenKey=>$token
        ];

        $filteredCredentials = [
            'user'      => 'michael',
            'password'  => '123'
        ];

        $authId = 44;

        $tokens->shouldReceive('getAuthIdByToken')
               ->with($token, Tokens::PASSWORD_RESET)
               ->andReturn($authId);

        $tokens->shouldReceive('invalidate')
               ->with($user, Tokens::PASSWORD_RESET, $token)
               ->never();

        $users->shouldReceive('retrieveByAuthId')
              ->with($authId)
              ->andReturn($user);

        $users->shouldReceive('save')
              ->with($user)
              ->never();

        $broker->reset($credentials);

    }

    protected function newBroker($userProvider=null, $tokenRepository=null,
                                 $userRepository=null)
    {
        $userProvider = $userProvider ?: $this->mockUserProvider();
        $tokenRepository = $tokenRepository ?: $this->mockTokens();
        $userRepository = $userRepository ?: $this->mockUsers();
        return new CredentialsBroker($userProvider, $tokenRepository, $userRepository);
    }

}

class PropertyOverloadedUser extends GenericUser
{
    public $attributes = [];

    public function __set($var, $value)
    {
        $this->attributes[$var] = $value;
    }
}


class ArrayAccessableUser extends GenericUser implements ArrayAccess
{
    public $attributes = [];

    #[ReturnTypeWillChange] public function offsetGet($var)
    {
        return $this->attributes[$var];
    }

    #[ReturnTypeWillChange] public function offsetSet($var, $value)
    {
        $this->attributes[$var] = $value;
    }

    #[ReturnTypeWillChange] public function offsetExists($var)
    {
        return isset($this->attributes[$var]);
    }

    #[ReturnTypeWillChange] public function offsetUnset($var)
    {
        unset($this->attributes[$var]);
    }
}
