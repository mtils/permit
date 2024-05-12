<?php

use Mockery as m;

use Permit\Authentication\CredentialsValidator;
use Permit\Hashing\NativeHasher;
use Permit\User\GenericUser;
use PHPUnit\Framework\TestCase;

class CredentialsValidatorTest extends TestCase
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Authentication\CredentialsValidatorInterface',
            $this->newValidator()
        );
    }

    public function testValidateCredentialsReturnNullIfNoPasswordPassed()
    {

        $validator = $this->newValidator();
        $user = $this->newUser();

        $this->assertNull($validator->validateCredentials($user, []));

    }

    public function testValidateCredentialsAsksHasherIfPrerequisitesGiven()
    {
        $hasher = $this->mockHasher();
        $validator = $this->newValidator($hasher);
        $credentials = ['login'=>'christine','password'=>'123'];
        $user = $this->newUser();
        $user->password = '456';

        $hasher->shouldReceive('check')
               ->with($credentials['password'], $user->password)
               ->once()
               ->andReturn(true);

        $this->assertTrue($validator->validateCredentials($user, $credentials));
    }

    public function testValidateCredentialsTakesGetAuthPasswordMethodIfPresent()
    {
        $hasher = $this->mockHasher();
        $validator = $this->newValidator($hasher);
        $credentials = ['login'=>'christine','password'=>'123'];
        $user = new GenericIlluminateUser;

        $hasher->shouldReceive('check')
               ->with($credentials['password'], $user->getAuthPassword())
               ->once()
               ->andReturn(true);

        $this->assertTrue($validator->validateCredentials($user, $credentials));
    }

    public function testValidateCredentialsTakesGetPasswordMethodIfPresent()
    {
        $hasher = $this->mockHasher();
        $validator = $this->newValidator($hasher);
        $credentials = ['login'=>'christine','password'=>'123'];
        $user = new GenericOtherUser;

        $hasher->shouldReceive('check')
               ->with($credentials['password'], $user->getPassword())
               ->once()
               ->andReturn(true);

        $this->assertTrue($validator->validateCredentials($user, $credentials));
    }

    public function testValidateCredentialsThrowsExceptionIfPasswordNotFound()
    {
        $this->expectException(\OutOfBoundsException::class);
        $hasher = $this->mockHasher();
        $validator = $this->newValidator($hasher);
        $credentials = ['login'=>'christine','password'=>'123'];
        $user = new GenericUser;

        $hasher->shouldReceive('check')
               ->never();

        $validator->validateCredentials($user, $credentials);
    }

    public function newValidator($hasher=null)
    {
        $hasher = $hasher ?: $this->newHasher();
        return new CredentialsValidator($hasher);
    }

    public function newHasher()
    {
        return new NativeHasher;
    }

    public function mockHasher()
    {
        return m::mock('Permit\Hashing\HasherInterface');
    }

    public function newUser()
    {
        return new GenericUser;
    }

    public function tearDown(): void
    {
        m::close();
    }

}

class GenericIlluminateUser extends GenericUser
{
    public function getAuthPassword()
    {
        return 'getAuthPassword';
    }
}

class GenericOtherUser extends GenericUser
{
    public function getPassword()
    {
        return 'getPassword';
    }
}
