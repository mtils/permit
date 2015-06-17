<?php

use Permit\Permission\MergedChecker;
use Permit\Permission\NoWildcardMerger;

if (!class_exists('AbstractCheckerTest')) {
    require_once(__DIR__.'/AbstractCheckerTest.php');
}

class MergedCheckerTest extends AbstractCheckerTest
{

    protected function newChecker($merger = null)
    {
        return new MergedChecker($merger ?: $this->newMerger());
    }

    protected function newMerger()
    {
        return new NoWildcardMerger();
    }

}