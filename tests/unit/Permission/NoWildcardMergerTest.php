<?php 

use Mockery as m;

use Permit\Permission\NoWildcardMerger;
use Permit\Permission\Holder\HolderInterface as Holder;

class NoWildcardMergerTest extends BaseTest
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Permission\MergerInterface',
            $this->newMerger()
        );
    }

    public function testToBoolPermissionsTransformsCorectToBool()
    {

        $merger = $this->newMerger();

        $permissions = [
            'cms.access' => Holder::INHERITED,
            'guest'      => Holder::GRANTED,
            'foo.bar'    => Holder::DENIED,
            'bar.foo'    => Holder::INHERITED,
        ];

        $awaited = [
            'guest'      => true,
            'foo.bar'    => false,
        ];

        $this->assertEquals($awaited, $merger->toBoolPermissions($permissions));

    }


    public function testCollectHolderCodesRemovesInherited()
    {

        $merger = $this->newMerger();

        $permissions = [
            'cms.access' => Holder::INHERITED,
            'guest'      => Holder::GRANTED,
            'foo.bar'    => Holder::DENIED,
            'bar.foo'    => Holder::INHERITED
        ];

        $awaited = [
            'guest'      => Holder::GRANTED,
            'foo.bar'    => Holder::DENIED
        ];

        $user = $this->newUser();

        $this->setHolderPermissions($user, $permissions);

        $this->assertEquals($merger->collectHolderCodes($user), $awaited);

    }

    public function testMergeSubHolderCodesMergesCorrectly()
    {

        $group1Permissions = [
            'cms.access' => Holder::GRANTED,
            'foo.bar'    => Holder::INHERITED,
            'bar.foo'    => Holder::DENIED,
            'some.perm'  => Holder::GRANTED,
            'other.perm' => Holder::GRANTED,
            'odd.perm'   => Holder::DENIED,
            'nonsense'   => Holder::INHERITED
        ];

        $group2Permissions = [
            'foo.bar'    => Holder::GRANTED,
            'group2.foo' => Holder::DENIED,
            'group2.bar' => Holder::GRANTED,
            'some.perm'  => Holder::DENIED,
            'odd.perm'   => Holder::GRANTED,
            'nonsense'   => Holder::INHERITED
        ];

        $awaited = [
            'cms.access' => Holder::GRANTED,
            'foo.bar'    => Holder::GRANTED,
            'bar.foo'    => Holder::DENIED,
            'some.perm'  => Holder::DENIED,
            'other.perm' => Holder::GRANTED,
            'group2.foo' => Holder::DENIED,
            'group2.bar' => Holder::GRANTED,
            'odd.perm'   => Holder::DENIED,
            'nonsense'   => Holder::INHERITED
        ];

        $group1 = $this->newGroup(1);
        $this->setHolderPermissions($group1, $group1Permissions);

        $group2 = $this->newGroup(2);
        $this->setHolderPermissions($group2, $group2Permissions);

        $user = $this->newUser();
        $user->attachGroup($group1);
        $user->attachGroup($group2);

        $merged = $this->newMerger()->mergeSubHolderCodes($user);

        foreach ($merged as $code=>$access) {
            $this->assertEquals($awaited[$code], $access);
        }

        foreach ($awaited as $code=>$access) {
            $this->assertEquals($merged[$code], $access);
        }

    }

    public function testMergeNestedMergesCorrectly()
    {

        $groupPermissions = [
            'cms.access' => Holder::GRANTED,
            'foo.bar'    => Holder::INHERITED,
            'bar.foo'    => Holder::DENIED,
            'some.perm'  => Holder::GRANTED,
            'other.perm' => Holder::GRANTED,
            'odd.perm'   => Holder::DENIED,
            'nonsense'   => Holder::INHERITED,
            'group.perm' => Holder::GRANTED
        ];

        $holderPermissions = [
            'foo.bar'    => Holder::GRANTED,
            'bar.foo'    => Holder::GRANTED,
            'group2.foo' => Holder::DENIED,
            'group2.bar' => Holder::GRANTED,
            'some.perm'  => Holder::DENIED,
            'odd.perm'   => Holder::GRANTED,
            'nonsense'   => Holder::INHERITED
        ];

        $awaited = [
            'cms.access' => Holder::GRANTED,
            'foo.bar'    => Holder::GRANTED,
            'bar.foo'    => Holder::GRANTED,
            'group2.foo' => Holder::DENIED,
            'group2.bar' => Holder::GRANTED,
            'some.perm'  => Holder::DENIED,
            'odd.perm'   => Holder::GRANTED,
            'other.perm' => Holder::GRANTED,
            'group.perm' => Holder::GRANTED
        ];

        $merged = $this->newMerger()->mergeNested($holderPermissions, $groupPermissions);

        foreach ($merged as $code=>$access) {
            $this->assertEquals($awaited[$code], $access);
        }

        foreach ($awaited as $code=>$access) {
            $this->assertEquals($merged[$code], $access);
        }

    }

    public function testGetMergedPermissionsPerformsAllTasks()
    {

        $group1Permissions = [
            'cms.access' => Holder::GRANTED,
            'foo.bar'    => Holder::INHERITED,
            'bar.foo'    => Holder::DENIED,
            'some.perm'  => Holder::GRANTED,
            'other.perm' => Holder::GRANTED,
            'odd.perm'   => Holder::DENIED,
            'nonsense'   => Holder::INHERITED
        ];

        $group2Permissions = [
            'foo.bar'    => Holder::GRANTED,
            'group2.foo' => Holder::DENIED,
            'group2.bar' => Holder::GRANTED,
            'some.perm'  => Holder::DENIED,
            'odd.perm'   => Holder::GRANTED,
            'nonsense'   => Holder::INHERITED
        ];

        $holderPermissions = [
            'foo.bar'    => Holder::DENIED,
            'bar.foo'    => Holder::GRANTED,
            'group2.foo' => Holder::DENIED,
            'some.perm'  => Holder::GRANTED,
            'odd.perm'   => Holder::INHERITED,
            'nonsense'   => Holder::GRANTED
        ];

        $awaited = [
            'cms.access' => true,
            'foo.bar'    => false,
            'bar.foo'    => true,
            'some.perm'  => true,
            'other.perm' => true,
            'group2.foo' => false,
            'group2.bar' => true,
            'odd.perm'   => false,
            'nonsense'   => true
        ];

        $group1 = $this->newGroup(1);
        $this->setHolderPermissions($group1, $group1Permissions);

        $group2 = $this->newGroup(2);
        $this->setHolderPermissions($group2, $group2Permissions);

        $user = $this->newUser();
        $this->setHolderPermissions($user, $holderPermissions);

        $user->attachGroup($group1);
        $user->attachGroup($group2);

        $merged = $this->newMerger()->getMergedPermissions($user);

        foreach ($merged as $code=>$access) {
            $this->assertEquals($awaited[$code], $access);
        }

        foreach ($awaited as $code=>$access) {
            $this->assertEquals($merged[$code], $access);
        }
    }

    protected function setHolderPermissions(Holder $holder, array $permissions)
    {
        foreach ($permissions as $code=>$access) {
            $holder->setPermissionAccess($code, $access);
        }
    }

    protected function newMerger()
    {
        return new NoWildcardMerger();
    }



    public function tearDown()
    {
        m::close();
    }

}