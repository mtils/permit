<?php 

use Mockery as m;
use Permit\Support\Laravel\Encryption\OpenSSLEncrypter;
use PHPUnit\Framework\TestCase;

class OpenSSLEncrypterTest extends TestCase
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Illuminate\Contracts\Encryption\Encrypter',
            $this->newEncrypter()
        );
    }

    public function testConvertToHexConvertsCorrectly()
    {
        $encrypter = $this->newEncrypter();

        $this->assertEquals('3132333435363738', $encrypter->stringToHex('12345678'));
        $this->assertEquals('E2', $encrypter->stringToHex('€'));

    }

    public function testToEncryptionKeyReturnsUnPaddedStringIfLengthMatches()
    {
        $encrypter = $this->newEncrypter();
        $this->assertEquals('3132333435363738', $encrypter->toEncryptionKey('12345678', 16));
        $this->assertEquals('E2', $encrypter->toEncryptionKey('€', 2));
    }

    public function testToEncryptionKeyReturnsPaddedStringIfTooLong()
    {
        $encrypter = $this->newEncrypter();
        $this->assertEquals('31323334', $encrypter->toEncryptionKey('12345678', 8));
        $this->assertEquals('E', $encrypter->toEncryptionKey('€', 1));
    }

    public function testToEncryptionKeyReturnsRepeatedStringIfTooShort()
    {
        $encrypter = $this->newEncrypter();
        $this->assertEquals('313233343536373831323334', $encrypter->toEncryptionKey('12345678', 24));
        $this->assertEquals('E2E2E2E', $encrypter->toEncryptionKey('€', 7));
    }

    public function testStringEncryption()
    {

        $encrypter = $this->newEncrypter();

        $tests = ['Foo-Bar','#**#+??00=)7899', 'öÄßÄli€'];

        foreach ($tests as $test) {
            $encrypted = $encrypter->encrypt($test);
            $this->assertNotEquals($encrypted, $test);
            $this->assertEquals($test, $encrypter->decrypt($encrypted));
        }

    }

    public function testBoolEncryption()
    {

        $encrypter = $this->newEncrypter();

        $tests = [true, false];

        foreach ($tests as $test) {
            $encrypted = $encrypter->encrypt($test);
            $this->assertNotEquals($encrypted, $test);
            $this->assertTrue($test === $encrypter->decrypt($encrypted));
        }

    }

    public function testFloatEncryption()
    {

        $encrypter = $this->newEncrypter();

        $tests = [26.4, 28.7];

        foreach ($tests as $test) {
            $encrypted = $encrypter->encrypt($test);
            $this->assertNotEquals($encrypted, $test);
            $this->assertTrue($test === $encrypter->decrypt($encrypted));
        }

    }

    public function testArrayEncryption()
    {

        $encrypter = $this->newEncrypter();

        $test = ['this'=>'is', 'a'=>'test'];

        $encrypted = $encrypter->encrypt($test);
        $this->assertNotEquals($encrypted, $test);
        $this->assertEquals($test, $encrypter->decrypt($encrypted));

    }

    protected function newEncrypter($key='9nwincrye3fidbAh')
    {
        return new OpenSSLEncrypter($key);
    }

    public function setUp()
    {
        mb_internal_encoding('UTF-8');
    }

    public function tearDown()
    {
        m::close();
    }

}
