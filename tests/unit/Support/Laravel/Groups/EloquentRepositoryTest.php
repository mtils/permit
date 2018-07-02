<?php 

use Mockery as m;

use Permit\Support\Laravel\Groups\EloquentRepository;
use Permit\Support\Laravel\Groups\EloquentGroup;
use PHPUnit\Framework\TestCase;

class EloquentRepositoryTest extends TestCase{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Groups\GroupRepositoryInterface',
            $this->newRepository()
        );
    }

    public function testFindByGroupIdForwardsToFind()
    {

        $groupModel = m::mock('Permit\Support\Laravel\Groups\EloquentGroup');
        $repo = $this->newRepository($groupModel);

        $resultGroup = $this->newGroup(15);

        $groupModel->shouldReceive('find')
                   ->with(15)
                   ->once()
                   ->andReturn($resultGroup);

        $this->assertSame($resultGroup, $repo->findByGroupId(15));

    }

    public function testGetNewForwardsToNewInstance()
    {

        $groupModel = m::mock('Permit\Support\Laravel\Groups\EloquentGroup');
        $repo = $this->newRepository($groupModel);

        $resultGroup = $this->newGroup(21);
        $attributes = ['name'=>'Moderators'];

        $groupModel->shouldReceive('newInstance')
                   ->with($attributes)
                   ->once()
                   ->andReturn($resultGroup);

        $this->assertSame($resultGroup, $repo->getNew($attributes));

    }

    public function testCreateCallsSave()
    {

        $groupModel = m::mock('Permit\Support\Laravel\Groups\EloquentGroup');
        $repo = $this->newRepository($groupModel);
        $resultGroup = m::mock('Permit\Support\Laravel\Groups\EloquentGroup');
        $attributes = ['name'=>'Moderators'];

        $groupModel->shouldReceive('newInstance')
                   ->with($attributes)
                   ->once()
                   ->andReturn($resultGroup);

        $resultGroup->shouldReceive('save')
                    ->andReturn(true);

        $this->assertSame($resultGroup, $repo->create($attributes));
    }

    public function testSaveCallsSave()
    {

        $groupModel = m::mock('Permit\Support\Laravel\Groups\EloquentGroup');
        $repo = $this->newRepository($this->newGroup());

        $savedGroup = m::mock('Permit\Support\Laravel\Groups\EloquentGroup');

        $savedGroup->shouldReceive('save')
                   ->andReturn(true);

        $this->assertTrue($repo->save($savedGroup));
    }

    public function testAllForwardsToModelAll()
    {

        $groupModel = m::mock('Permit\Support\Laravel\Groups\EloquentGroup');
        $repo = $this->newRepository($groupModel);

        $result = [$this->newGroup(1), $this->newGroup(2)];

        $groupModel->shouldReceive('all')
                   ->andReturn($result);

        $this->assertSame($result, $repo->all());

    }

    public function tearDown()
    {
        m::close();
    }

    public function newRepository($group=null)
    {
        $group = $group ?: $this->newGroup();
        return new EloquentRepository($group);
    }

    public function newGroup($id=1){
        $group = new EloquentGroup;
        $group->id = $id;
        return $group;
    }

}
