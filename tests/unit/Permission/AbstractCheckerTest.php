<?php 

use Permit\Permission\AccessChecker;
use Permit\Access\CheckerInterface;
use Permit\User\GenericUser;
use Permit\Permission\Holder\HolderInterface;
use Permit\Permission\PermissionableInterface;
use Permit\Permission\GenericPermissionableTrait;
use Permit\Groups\GenericGroup;
use PHPUnit\Framework\TestCase;

abstract class AbstractCheckerTest extends TestCase
{

    public function testImplementsInterface()
    {

        $checker = $this->newChecker();

        $this->assertInstanceOf('Permit\Access\CheckerInterface', $checker);

    }

    public function testHasAccessReturnsNullIfNotGranted()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();

        $this->assertNull($checker->hasAccess($user, 'test.access'));

    }

    public function testHasAccessReturnsFalseIfDenied()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();

        $user->setPermissionAccess('test.access', HolderInterface::DENIED);

        $this->assertFalse($checker->hasAccess($user, 'test.access'));

    }

    public function testHasAccessReturnsNullIfInherited()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();

        $user->setPermissionAccess('test.access', HolderInterface::INHERITED);

        $this->assertNull($checker->hasAccess($user, 'test.access'));

    }

    public function testHasAccessReturnsTrueIfGranted()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.modify', HolderInterface::GRANTED);

        $this->assertTrue($checker->hasAccess($user, 'test.modify'));

    }

    public function testHasAccessReturnsTrueIfSuperUser()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();
        $user->setPermissionAccess('superuser', HolderInterface::GRANTED);

        $this->assertTrue($checker->hasAccess($user, 'test.modify'));

    }

    public function testHasPermissionAccessReturnsTrueIfSuperUser()
    {

        $user = $this->newUser();
        $superUser = $this->newUser();
        $superUser->setIsSystem(true);
        $checker = $this->newChecker();

        $this->assertNull($checker->hasAccess($user, 'test.modify'));
        $this->assertTrue($checker->hasAccess($superUser, 'test.modify'));


    }

    public function testPermissonInSubHoldersAppliesIfNotInHolder()
    {

        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $user = $this->newUser();
        $user->attachGroup($moderatorGroup);
        $user->attachGroup($publisherGroup);
        $user->attachGroup($maintainerGroup);
        $code = 'cms.access';
        $code2 = 'newsletter.send';

        $moderatorGroup->setPermissionAccess($code, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code, HolderInterface::GRANTED);
        $maintainerGroup->setPermissionAccess($code, HolderInterface::INHERITED);

        $moderatorGroup->setPermissionAccess($code2, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code2, HolderInterface::GRANTED);
        $maintainerGroup->setPermissionAccess($code2, HolderInterface::DENIED);

        $this->assertTrue($checker->hasAccess($user, $code));
        $this->assertFalse($checker->hasAccess($user, $code2));

    }

    public function testPermissonInSubHoldersAppliesIfInheritedInHolder()
    {
        
        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $user = $this->newUser();
        $user->attachGroup($moderatorGroup);
        $user->attachGroup($publisherGroup);
        $user->attachGroup($maintainerGroup);
        $code = 'cms.access';
        $code2 = 'newsletter.send';
    
        $user->setPermissionAccess($code, HolderInterface::INHERITED);
        $user->setPermissionAccess($code2, HolderInterface::INHERITED);
        $moderatorGroup->setPermissionAccess($code, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code, HolderInterface::GRANTED);
        $maintainerGroup->setPermissionAccess($code, HolderInterface::INHERITED);

        $moderatorGroup->setPermissionAccess($code2, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code2, HolderInterface::GRANTED);
        $maintainerGroup->setPermissionAccess($code2, HolderInterface::DENIED);


        $this->assertTrue($checker->hasAccess($user, $code));
        $this->assertFalse($checker->hasAccess($user, $code2));
        
    }

    public function testPermissonInSubHoldersDoesNotApplyIfGrantedInHolder()
    {
        
        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $user = $this->newUser();
        $user->attachGroup($moderatorGroup);
        $user->attachGroup($publisherGroup);
        $user->attachGroup($maintainerGroup);
        $code = 'cms.access';
        $code2 = 'newsletter.send';
    
        $user->setPermissionAccess($code, HolderInterface::GRANTED);
        $user->setPermissionAccess($code2, HolderInterface::GRANTED);
        $moderatorGroup->setPermissionAccess($code, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code, HolderInterface::INHERITED);
        $maintainerGroup->setPermissionAccess($code, HolderInterface::INHERITED);

        $moderatorGroup->setPermissionAccess($code2, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code2, HolderInterface::DENIED);
        $maintainerGroup->setPermissionAccess($code2, HolderInterface::INHERITED);

        $this->assertTrue($checker->hasAccess($user, $code));
        $this->assertTrue($checker->hasAccess($user, $code2));
        
    }

    public function testPermissonInSubHoldersDoesNotApplyIfDeniedInHolder()
    {
        
        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $user = $this->newUser();
        $user->attachGroup($moderatorGroup);
        $user->attachGroup($publisherGroup);
        $user->attachGroup($maintainerGroup);
        $code = 'cms.access';
        $code2 = 'newsletter.send';
    
        $user->setPermissionAccess($code, HolderInterface::DENIED);
        $user->setPermissionAccess($code2, HolderInterface::DENIED);
        $moderatorGroup->setPermissionAccess($code, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code, HolderInterface::GRANTED);
        $maintainerGroup->setPermissionAccess($code, HolderInterface::INHERITED);

        $moderatorGroup->setPermissionAccess($code2, HolderInterface::DENIED);
        $publisherGroup->setPermissionAccess($code2, HolderInterface::DENIED);
        $maintainerGroup->setPermissionAccess($code2, HolderInterface::DENIED);

        $this->assertFalse($checker->hasAccess($user, $code));
        $this->assertFalse($checker->hasAccess($user, $code2));
        
    }

    public function testHasAccessAllowsOnlyIfAllPassedCodesAreGranted()
    {
        
        $checker = $this->newChecker();
        $user = $this->newUser();
        $code = 'cms.access';
        $code2 = 'newsletter.send';
        $code3 = 'user.activate';

        $user->setPermissionAccess($code, HolderInterface::GRANTED);
        $user->setPermissionAccess($code2, HolderInterface::GRANTED);
        $user->setPermissionAccess($code3, HolderInterface::DENIED);

        $this->assertFalse($checker->hasAccess($user,[$code, $code2, $code3]));
        $this->assertTrue($checker->hasAccess($user,[$code, $code2]));
        $this->assertFalse($checker->hasAccess($user,[$code, $code3]));

    }

    public function testHasAccessSupportsPermissionableInterface()
    {
        
        $checker = $this->newChecker();
        $user = $this->newUser();
        

        $code = 'cms.access';
        $code2 = 'newsletter.send';
        $code3 = 'user.activate';

        $user->setPermissionAccess($code, HolderInterface::GRANTED);
        $user->setPermissionAccess($code2, HolderInterface::GRANTED);
        $user->setPermissionAccess($code3, HolderInterface::DENIED);

        $permissionable = $this->newPermissionable([$code, $code2, $code3]);
        $permissionable2 = $this->newPermissionable([$code, $code2]);
        $permissionable3 = $this->newPermissionable([$code, $code3]);

        $this->assertFalse($checker->hasAccess($user, $permissionable));
        $this->assertTrue($checker->hasAccess($user, $permissionable2));
        $this->assertFalse($checker->hasAccess($user, $permissionable3));

    }

    abstract protected function newChecker();

    protected function newUser()
    {
        return new GenericUser;
    }

    protected function newGroup()
    {
        return new GenericGroup;
    }

    protected function newPermissionable(array $requiredCodes=[]){
        $permissionable = new Permissionable;
        $permissionable->setRequiredPermissionCodes($requiredCodes);
        return $permissionable;
    }

}

class Permissionable implements PermissionableInterface{
    use GenericPermissionableTrait;
}
