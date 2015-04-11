<?php 

require_once __DIR__.'/BaseGeneratorTest.php';


use Mockery as m;

use Permit\Random\OpenSSLGenerator;

class OpenSSLGeneratorTest extends BaseGeneratorTest{

    public function newGenerator()
    {
        return new OpenSSLGenerator();
    }

}