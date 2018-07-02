<?php 

use PHPUnit\Framework\TestCase;

abstract class BaseHasherTest extends TestCase{

    public function testImplementsInterface()
    {
        $this->assertInstanceof(
            'Permit\Hashing\HasherInterface',
            $this->newHasher()
        );
    }

    public function testHashReturnsValue()
    {
        $hasher = $this->newHasher();
        $plain = 'My little pony';

        $hash = $hasher->hash($plain);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($plain, $hash);
    }

    public function testCheckReturnsTrueOnMatch()
    {

        $hasher = $this->newHasher();
        $plain = 'My little pony';

        $hash = $hasher->hash($plain);

        $this->assertNotEquals($plain, $hash);
        $this->assertTrue($hasher->check($plain, $hash));
    }

    public function testCheckReturnsFalseOnNoMatch()
    {
        $hasher = $this->newHasher();
        $plain = 'My little pony';

        $hash = md5($plain);

        $this->assertNotEquals($plain, $hash);
        $this->assertFalse($hasher->check($plain, $hash));
    }

    public function testNeedsRehashReturnsFalseIfMatched()
    {
        $hasher = $this->newHasher();
        $plain = 'My little pony';

        $hash = $hasher->hash($plain);

        $this->assertNotEquals($plain, $hash);
        $this->assertTrue($hasher->check($plain, $hash));
        $this->assertFalse($hasher->needsRehash($hash));
    }

    public function testNeedsRehashReturnsTrueIfBadHash()
    {
        $hasher = $this->newHasher();
        $plain = 'My little pony';

        $hash = md5($plain);

        $this->assertNotEquals($plain, $hash);
        $this->assertTrue($hasher->needsRehash($hash));
    }

    abstract public function newHasher();

}
