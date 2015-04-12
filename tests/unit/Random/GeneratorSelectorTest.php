<?php 

use Mockery as m;

use Permit\Random\GeneratorSelector;

class GeneratorSelectorTest extends PHPUnit_Framework_TestCase{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Random\GeneratorInterface',
            $this->newGenerator()
        );
    }

    public function testAddGenerator()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();

        $selector->add($generator);

        $this->assertEquals([$generator],$selector->getGenerators());

        $generator2 = $this->mock();

        $selector->add($generator2);

        $this->assertEquals([$generator, $generator2],$selector->getGenerators());

    }

    public function testRemoveGenerator()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();
        $generator->id = 1;
        $generator2 = $this->mock();
        $generator2->id = 2;

        $selector->add($generator);
        $selector->add($generator2);

        $this->assertEquals([$generator, $generator2], $selector->getGenerators());

        $selector->remove($generator);

        $this->assertEquals([$generator2], $selector->getGenerators());

        $selector->remove($generator2);

        $this->assertEquals([], $selector->getGenerators());

    }

    public function testGetStrongestGeneratorReturnStrongest()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();
        $generator2 = $this->mock();
        $generator3 = $this->mock();

        $selector->add($generator);
        $selector->add($generator2);
        $selector->add($generator3);

        $generator->shouldReceive('isSupported')
                  ->andReturn(true);
        $generator2->shouldReceive('isSupported')
                   ->andReturn(true);
        $generator3->shouldReceive('isSupported')
                   ->andReturn(true);

        $generator->shouldReceive('getStrength')
                  ->andReturn(5);
        $generator2->shouldReceive('getStrength')
                   ->andReturn(25);
        $generator3->shouldReceive('getStrength')
                   ->andReturn(10);

        $this->assertSame($generator2, $selector->getStrongestGenerator());

    }

    public function testGetStrongestGeneratorSearchesOnlyOnce()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();
        $generator2 = $this->mock();
        $generator3 = $this->mock();

        $selector->add($generator);
        $selector->add($generator2);
        $selector->add($generator3);

        $generator->shouldReceive('isSupported')
                  ->andReturn(true);
        $generator2->shouldReceive('isSupported')
                   ->andReturn(true);
        $generator3->shouldReceive('isSupported')
                   ->andReturn(true);

        $generator->shouldReceive('getStrength')
                  ->once()
                  ->andReturn(5);
        $generator2->shouldReceive('getStrength')
                   ->once()
                   ->andReturn(25);
        $generator3->shouldReceive('getStrength')
                   ->once()
                   ->andReturn(10);

        $this->assertSame($generator2, $selector->getStrongestGenerator());

        $this->assertSame($generator2, $selector->getStrongestGenerator());

    }

    public function testGetStrongestGeneratorDoesntChooseUnsupported()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();
        $generator2 = $this->mock();
        $generator3 = $this->mock();

        $selector->add($generator);
        $selector->add($generator2);
        $selector->add($generator3);

        $generator->shouldReceive('isSupported')
                  ->andReturn(true);
        $generator2->shouldReceive('isSupported')
                   ->andReturn(FALSE);
        $generator3->shouldReceive('isSupported')
                   ->andReturn(true);

        $generator->shouldReceive('getStrength')
                  ->andReturn(5);
        $generator2->shouldReceive('getStrength')
                   ->andReturn(25);
        $generator3->shouldReceive('getStrength')
                   ->andReturn(10);

        $this->assertSame($generator3, $selector->getStrongestGenerator());

    }

    /**
     * @expectedException UnderflowException
     **/
    public function testGetStrongestGeneratorThrowsExceptionIfNoneSupportedFound()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();

        $selector->add($generator);
        $generator->shouldReceive('isSupported')
                  ->andReturn(false);

        $selector->getStrongestGenerator();

    }

    /**
     * @expectedException UnderflowException
     **/
    public function testGetStrongestGeneratorThrowsExceptionIfNoGeneratoryAdded()
    {
        $selector = $this->newGenerator();
        $selector->getStrongestGenerator();

    }

    public function testGetStrengthForwardsToStrongest()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();

        $selector->add($generator);

        $generator->shouldReceive('isSupported')
                  ->andReturn(true);
        $generator->shouldReceive('getStrength')
                  ->twice()
                  ->andReturn(5);

        $this->assertEquals(5, $selector->getStrength());

    }

    public function testGenerateStringForwardsToStrongest()
    {
        $selector = $this->newGenerator();
        $generator = $this->mock();
        $params = [48,false];

        $selector->add($generator);

        $generator->shouldReceive('isSupported')
                  ->andReturn(true);
        $generator->shouldReceive('getStrength')
                  ->once()
                  ->andReturn(5);
        $generator->shouldReceive('generate')
                  ->with($params[0], $params[1])
                  ->once()
                  ->andReturn('test');

        $this->assertEquals(
            'test',
            $selector->generate($params[0],$params[1])
        );

    }

    public function testIsSupportedReturnsTrueIfGeneratorFound()
    {

        $selector = $this->newGenerator();
        $generator = $this->mock();

        $selector->add($generator);

        $generator->shouldReceive('isSupported')
                  ->andReturn(true);
        $generator->shouldReceive('getStrength')
                  ->once()
                  ->andReturn(5);

        $this->assertTrue($selector->isSupported());

    }

    public function testIsSupportedReturnsFalseIfNoGeneratorFound()
    {

        $selector = $this->newGenerator();

        $this->assertFalse($selector->isSupported());

    }

    public function newGenerator()
    {
        return new GeneratorSelector();
    }

    public function mock()
    {
        return m::mock('Permit\Random\GeneratorInterface');
    }

    public function tearDown()
    {
        m::close();
    }

}