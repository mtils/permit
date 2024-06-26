<?php

use Mockery as m;

use Permit\Random\OpenSSLGenerator;
use PHPUnit\Framework\TestCase;

abstract class BaseGeneratorTest extends TestCase{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Random\GeneratorInterface',
            $this->newGenerator()
        );
    }

    public function testCorrectAsciiStrlen()
    {
        $generator = $this->newGenerator();

        for ($i = 1; $i < 82; $i++) {
            $randomString = $generator->generate($i, true);
            $this->assertEquals($i, strlen($generator->generate($i, true)));

            //Just test the binary call also returns _something_
            $this->assertNotEmpty($i, strlen($generator->generate($i, false)));
        }

    }

    public function testGetStrengthReturnsIntBetween1And100()
    {
        $generator = $this->newGenerator();

        $strength = $generator->getStrength();

        $this->assertTrue(is_int($strength));
        $this->assertGreaterThan(0, $strength);
        $this->assertLessThan(101, $strength);
    }

    public function testIsSupportedReturnsBool()
    {
        $generator = $this->newGenerator();
        $this->assertIsBool($generator->isSupported());
    }

    public function testLengthLowerThanZeroThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $generator = $this->newGenerator();

        $generator->generate(0, true);
    }

    abstract public function newGenerator();

}
