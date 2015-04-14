<?php 

use Mockery as m;

use Permit\Hashing\NativeHasher;

require_once __DIR__.'/BaseHasherTest.php';


class NativeHasherTest extends BaseHasherTest{


    /**
     * @expectedException \RuntimeException
     **/
    public function testHashThrowsExceptionIfSystemHasNoSupport()
    {
        $adapter = $this->mockSystemAdapter();
        $hasher = new NativeHasher($adapter);

        $adapter->shouldReceive('isSupported')
                ->once()
                ->andReturn(false);

        $hasher->hash('foo');

    }

    /**
     * @expectedException \RuntimeException
     **/
    public function testCheckThrowsExceptionIfSystemHasNoSupport()
    {
        $adapter = $this->mockSystemAdapter();
        $hasher = new NativeHasher($adapter);

        $adapter->shouldReceive('isSupported')
                ->once()
                ->andReturn(false);

        $hasher->check('foo','bar');

    }

    /**
     * @expectedException \RuntimeException
     **/
    public function testNeedsRehashThrowsExceptionIfSystemHasNoSupport()
    {
        $adapter = $this->mockSystemAdapter();
        $hasher = new NativeHasher($adapter);

        $adapter->shouldReceive('isSupported')
                ->once()
                ->andReturn(false);

        $hasher->needsRehash('foo');

    }

    /**
     * @expectedException \RuntimeException
     **/
    public function testHashThrowsExceptionIfHashingFailed()
    {
        $adapter = $this->mockSystemAdapter();
        $hasher = new NativeHasher($adapter);

        $adapter->shouldReceive('isSupported')
                ->once()
                ->andReturn(true);

        $adapter->shouldReceive('passwordHash')
                ->once()
                ->andReturn(false);

        $hasher->hash('foo');
    }

    public function newHasher()
    {
        return new NativeHasher;
    }

    public function mockSystemAdapter(){
        // Trigger autload
        $hasher = new NativeHasher();
        return m::mock('Permit\Hashing\NativeHasherSystemAdapter');
    }

    public function tearDown()
    {
        m::close();
    }

}