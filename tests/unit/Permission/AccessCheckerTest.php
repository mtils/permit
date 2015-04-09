<?php 

use Permit\Permission\AccessChecker;
use Permit\Access\CheckerInterface;
use Permit\User\GenericUser;
use Permit\Permission\Holder\HolderInterface;

class AccessCheckerTest extends PHPUnit_Framework_TestCase{

    public function testImplementsInterface(){

        $checker = $this->newChecker();

        $this->assertInstanceOf('Permit\Access\CheckerInterface', $checker);

    }

    public function testHasAccessReturnsFalseIfNotGranted(){

        $user = new GenericUser;
        $checker = $this->newChecker();

        $this->assertFalse($checker->hasPermissionAccess($user, 'test.access'));

    }

    public function testHasAccessReturnsFalseIfDenied(){

        $user = new GenericUser;
        $checker = $this->newChecker();

        $user->setPermissionAccess('test.access', HolderInterface::DENIED);

        $this->assertFalse($checker->hasPermissionAccess($user, 'test.access'));

    }

    public function testHasAccessReturnsFalseIfInherited(){

        $user = new GenericUser;
        $checker = $this->newChecker();

        $user->setPermissionAccess('test.access', HolderInterface::INHERITED);

        $this->assertFalse($checker->hasPermissionAccess($user, 'test.access'));

    }

    public function testHasAccessReturnsTrueIfGranted(){

        $user = new GenericUser;
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.modify', HolderInterface::GRANTED);

        $this->assertTrue($checker->hasPermissionAccess($user, 'test.modify'));

    }

    public function testHasAccessReturnsTrueIfFuzzyGranted(){

        $user = new GenericUser;
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.*', HolderInterface::GRANTED);

        $this->assertTrue($checker->hasPermissionAccess($user, 'test.modify'));

    }

    public function testHasAccessReturnsTrueIfFuzzyDenied(){

        $user = new GenericUser;
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.*', HolderInterface::DENIED);

        $this->assertFalse($checker->hasPermissionAccess($user, 'test.modify'));

    }

    public function testHasAccessReturnsTrueIfFuzzyInherited(){

        $user = new GenericUser;
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.*', HolderInterface::INHERITED);

        $this->assertFalse($checker->hasPermissionAccess($user, 'test.modify'));

    }

    protected function newChecker(){
        return new AccessChecker;
    }

}