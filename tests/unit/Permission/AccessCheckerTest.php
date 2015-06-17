<?php 

use Permit\Permission\AccessChecker;
use Permit\Access\CheckerInterface;
use Permit\User\GenericUser;
use Permit\Permission\Holder\HolderInterface;
use Permit\Permission\PermissionableInterface;
use Permit\Permission\GenericPermissionableTrait;
use Permit\Groups\GenericGroup;

if (!class_exists('AbstractCheckerTest')) {
    require_once(__DIR__.'/AbstractCheckerTest.php');
}

class AccessCheckerTest extends AbstractCheckerTest
{

    public function testCreateFuzzyCodeReturnsCorrectCode()
    {
        
        $checker = $this->newChecker();

        $this->assertEquals('cms.*', $checker->getFuzzyCode('cms.access'));
        $this->assertEmpty($checker->getFuzzyCode('cms'));
        $this->assertEquals('cms.page.*', $checker->getFuzzyCode('cms.page.edit'));

    }

    public function testHasAccessReturnsTrueIfFuzzyGranted()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.*', HolderInterface::GRANTED);

        $this->assertTrue($checker->hasAccess($user, 'test.modify'));

    }

    public function testHasAccessReturnsTrueIfFuzzyDenied()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.*', HolderInterface::DENIED);

        $this->assertFalse($checker->hasAccess($user, 'test.modify'));

    }

    public function testHasAccessReturnsTrueIfFuzzyInherited()
    {

        $user = $this->newUser();
        $checker = $this->newChecker();
        $user->setPermissionAccess('test.*', HolderInterface::INHERITED);

        $this->assertNull($checker->hasAccess($user, 'test.modify'));

    }

    public function testGetMergedSubHolderAccessReturnsInheritedIfCodeNotFound()
    {
        
        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $groups = [$moderatorGroup, $publisherGroup, $maintainerGroup];

        $this->assertSame(
            HolderInterface::INHERITED,
            $checker->getMergedSubHoldersAccess($groups, 'cms.access')
        );
        
    }

    public function testGetMergedSubHolderAccessReturnsGrantedIfOneGranted()
    {
        
        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $groups = [$moderatorGroup, $publisherGroup, $maintainerGroup];
        $code = 'cms.access';
    

        $publisherGroup->setPermissionAccess($code, HolderInterface::GRANTED);

        $this->assertSame(
            HolderInterface::GRANTED,
            $checker->getMergedSubHoldersAccess($groups, $code)
        );
        
    }

    public function testGetMergedSubHolderAccessReturnsGrantedIfOneGrantedAndOthersInherited()
    {
        
        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $groups = [$moderatorGroup, $publisherGroup, $maintainerGroup];
        $code = 'cms.access';
    

        $moderatorGroup->setPermissionAccess($code, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code, HolderInterface::GRANTED);
        $maintainerGroup->setPermissionAccess($code, HolderInterface::INHERITED);

        $this->assertSame(
            HolderInterface::GRANTED,
            $checker->getMergedSubHoldersAccess($groups, $code)
        );
        
    }

    public function testGetMergedSubHolderAccessReturnsDeniedIfOneGrantedAndOtherDenied()
    {
        
        $checker = $this->newChecker();
        $moderatorGroup = $this->newGroup();
        $publisherGroup = $this->newGroup();
        $maintainerGroup = $this->newGroup();
        $groups = [$moderatorGroup, $publisherGroup, $maintainerGroup];
        $code = 'cms.access';
    

        $moderatorGroup->setPermissionAccess($code, HolderInterface::INHERITED);
        $publisherGroup->setPermissionAccess($code, HolderInterface::GRANTED);
        $maintainerGroup->setPermissionAccess($code, HolderInterface::DENIED);

        $this->assertSame(
            HolderInterface::DENIED,
            $checker->getMergedSubHoldersAccess($groups, $code)
        );
        
    }

    protected function newChecker()
    {
        return new AccessChecker;
    }

}
