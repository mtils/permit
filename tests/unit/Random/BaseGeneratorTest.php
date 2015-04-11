<?php 

use Mockery as m;

use Permit\Random\OpenSSLGenerator;

abstract class BaseGeneratorTest extends PHPUnit_Framework_TestCase{

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
        }

    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testLengthLowerThanZeroThrowsException()
    {
        $generator = $this->newGenerator();

        $generator->generate(0, true);
    }

    abstract public function newGenerator();

}