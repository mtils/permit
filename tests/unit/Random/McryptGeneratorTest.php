<?php 

require_once __DIR__.'/BaseGeneratorTest.php';

use Mockery as m;

use Permit\Random\McryptGenerator;

class McryptGeneratorTest extends BaseGeneratorTest{

    public function newGenerator()
    {
        return new McryptGenerator();
    }

}