<?php 

require_once __DIR__.'/BaseGeneratorTest.php';


use Mockery as m;

use Permit\Random\StrShuffleGenerator;

class StrShuffleGeneratorTest extends BaseGeneratorTest
{

    public function newGenerator()
    {
        return new StrShuffleGenerator();
    }

}