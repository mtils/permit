<?php 

use Mockery as m;

use Permit\Support\Laravel\Groups\EloquentHolderTrait;
use Permit\Groups\HolderInterface;
use Permit\Permission\Holder\NestedHolderInterface;
use Permit\Groups\GenericGroup;

require_once __DIR__.'/../../../../helpers/EloquentModel.php';

class EloquentHolderTraitTest extends PHPUnit_Framework_TestCase{

    public function testImplementsInterface()
    {

        $this->assertInstanceOf(
            'Permit\Groups\HolderInterface',
            $this->newUser()
        );

    }

    public function testImplementsNestedPermissionHolderInterface()
    {

        $this->assertInstanceOf(
            'Permit\Permission\Holder\NestedHolderInterface',
            $this->newUser()
        );

    }

    public function testGroupModelClassSetting(){

        $this->assertEquals(
            'App\User',
            UserGroupHolder::getGroupModelClass()
        );

        $this->assertEquals(
            'My\Secret\Lib\User',
            ConfiguredUserGroupHolder::getGroupModelClass()
        );

    }

    public function testGroupPivotTableSetting(){

        $this->assertEquals(
            'users_groups',
            UserGroupHolder::getGroupPivotTable()
        );

        $this->assertEquals(
            'user_group_pivot',
            ConfiguredUserGroupHolder::getGroupPivotTable()
        );

    }

    public function testAttachGroup(){

        $user = $this->newUser();

        $moderatorGroup = $this->newGroup(1);
        $publisherGroup = $this->newGroup(2);
        $testerGroup = $this->newGroup(3);

        $user->belongsToMany()->shouldReceive('getResults')->andReturn([]);

        $user->belongsToMany()
            ->shouldReceive('attach')
            ->with($moderatorGroup)
            ->once();
        $user->attachGroup($moderatorGroup);

        $user->belongsToMany()
            ->shouldReceive('attach')
            ->with($publisherGroup)
            ->once();
        $user->attachGroup($publisherGroup);

        $user->belongsToMany()
            ->shouldReceive('attach')
            ->with($testerGroup)
            ->once();
        $user->attachGroup($testerGroup);

    }

    public function testAttachGroupDoesNothingIfGroupAlreadyAssigned(){

        $user = $this->newUser();

        $moderatorGroup = $this->newGroup(1);

        $user->belongsToMany()->shouldReceive('getResults')->andReturn([$moderatorGroup]);

        $user->belongsToMany()
            ->shouldReceive('attach')
            ->with($moderatorGroup)
            ->never();
        $user->attachGroup($moderatorGroup);

    }

    public function testIsInGroup(){

        $user = $this->newUser();

        $moderatorGroup = $this->newGroup(1);
        $publisherGroup = $this->newGroup(2);

        $user->belongsToMany()->shouldReceive('getResults')->andReturn([$moderatorGroup]);

        $this->assertTrue($user->isInGroup($moderatorGroup));
        $this->assertFalse($user->isInGroup($publisherGroup));


    }

    public function testDetachGroup(){

        $user = $this->newUser();

        $moderatorGroup = $this->newGroup(1);

        $user->belongsToMany()->shouldReceive('getResults')->andReturn([$moderatorGroup]);

        $user->belongsToMany()
            ->shouldReceive('detach')
            ->with($moderatorGroup)
            ->once();
        $user->detachGroup($moderatorGroup);


    }

    public function testDetachGroupDoesNothingIfGroupNotAttached(){

        $user = $this->newUser();

        $moderatorGroup = $this->newGroup(1);
        $publisherGroup = $this->newGroup(2);
        $testerGroup = $this->newGroup(3);

        $user->belongsToMany()->shouldReceive('getResults')->andReturn([$publisherGroup]);

        $user->belongsToMany()
            ->shouldReceive('detach')
            ->with($moderatorGroup)
            ->never();
        $user->detachGroup($moderatorGroup);

    }

    public function newGroup($id=null)
    {
        $group = new GenericGroup;
        $group->setGroupId($id);
        return $group;
    }

    public function newUser()
    {
        return new UserGroupHolder;
    }

    public function tearDown()
    {
        m::close();
    }

}

class UserGroupHolder extends EloquentModel implements HolderInterface, NestedHolderInterface
{
    use EloquentHolderTrait;

    protected $groupRelation;

    public $attachedGroups = [];

    public function __construct(){
        $this->groupRelation = m::mock();
    }

    public function belongsToMany(){
        return $this->groupRelation;
    }
}

class ConfiguredUserGroupHolder extends EloquentModel implements HolderInterface, NestedHolderInterface
{

    use EloquentHolderTrait;

    static $groupModelClass = 'My\Secret\Lib\User';
    static $groupsPivotTable = 'user_group_pivot';

}