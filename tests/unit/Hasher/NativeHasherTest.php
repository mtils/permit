<?php

use Mockery as m;

use Permit\Hashing\NativeHasher;

require_once __DIR__.'/BaseHasherTest.php';


class NativeHasherTest extends BaseHasherTest{


    public function testHashThrowsExceptionIfSystemHasNoSupport()
    {
        $this->expectException(\RuntimeException::class);
        $adapter = $this->mockSystemAdapter();
        $hasher = new NativeHasher($adapter);

        $adapter->shouldReceive('isSupported')
                ->once()
                ->andReturn(false);

        $hasher->hash('foo');

    }

    public function testCheckThrowsExceptionIfSystemHasNoSupport()
    {
        $this->expectException(\RuntimeException::class);
        $adapter = $this->mockSystemAdapter();
        $hasher = new NativeHasher($adapter);

        $adapter->shouldReceive('isSupported')
                ->once()
                ->andReturn(false);

        $hasher->check('foo','bar');

    }

    public function testNeedsRehashThrowsExceptionIfSystemHasNoSupport()
    {
        $this->expectException(\RuntimeException::class);
        $adapter = $this->mockSystemAdapter();
        $hasher = new NativeHasher($adapter);

        $adapter->shouldReceive('isSupported')
                ->once()
                ->andReturn(false);

        $hasher->needsRehash('foo');

    }

    public function testHashThrowsExceptionIfHashingFailed()
    {
        $this->expectException(\RuntimeException::class);
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

    public function tearDown(): void
    {
        m::close();
    }

}