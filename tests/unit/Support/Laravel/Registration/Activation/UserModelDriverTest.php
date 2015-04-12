<?php 

use Mockery as m;

use Illuminate\Database\Eloquent\Model;

use Permit\Support\Laravel\Registration\Activation\UserModelDriver;
use Permit\Support\Laravel\User\EloquentUserTrait;
use Permit\User\UserInterface;

require_once __DIR__.'/../../../../../helpers/EloquentModel.php';

class UserModelDriverTest extends PHPUnit_Framework_TestCase{

    public function testImplementsInterface(){
        $this->assertInstanceOf(
            'Permit\Registration\Activation\DriverInterface',
            $this->newDriver()
        );
    }

    public function testReserveActivationAssignsActivationCodeToCorrectColumn()
    {

        $generator = $this->newCodeGenerator();
        $driver = $this->newDriver(null, $generator);
        $user = m::mock('ActivatableUser');
        $user->exists = true;

        $activationCodeColumn = 'cryptic_act_col';
        $code = $this->newActivationCode();

        $generator->shouldReceive('generate')
                  ->andReturn($code);

        $user->shouldReceive('setAttribute')
             ->with($activationCodeColumn, $code)
             ->once();

        $user->shouldReceive('save')
             ->once()
             ->andReturn(true);

        $driver->activationCodeColumn = $activationCodeColumn;
        $this->assertTrue($driver->reserveActivation($user));
    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testReserveActivationWithWrongModelThrowsException()
    {

        $generator = $this->newCodeGenerator();
        $driver = $this->newDriver(null, $generator);
        $user = m::mock('UnActivatableUser');
        $user->exists = true;

        $driver->reserveActivation($user);
    }

    /**
     * @expectedException \RuntimeException
     **/
    public function testReserveActivationWithNotExistingUserThrowsException()
    {

        $generator = $this->newCodeGenerator();
        $driver = $this->newDriver(null, $generator);
        $user = m::mock('ActivatableUser');
        $user->exists = false;

        $driver->reserveActivation($user);
    }

    /**
     * @expectedException \Permit\Registration\Activation\ActivationDataInvalidException
     **/
    public function testGetUserByActivationDataThrowsExceptionOnInvalidActivationData()
    {

        $driver = $this->newDriver();
        $driver->getUserByActivationData([]);
    }

    /**
     * @expectedException \Permit\Registration\Activation\ActivationDataInvalidException
     **/
    public function testGetUserByActivationDataThrowsExceptionOnWrongKeyLength()
    {

        $driver = $this->newDriver();
        $driver->activationKeyLength = 22;
        $driver->getUserByActivationData(['code',$this->newActivationCode()]);
    }

    /**
     * @expectedException \Permit\User\UserNotFoundException
     **/
    public function testGetUserByActivationDataThrowsExceptionIfNoUserFound()
    {

        $userModel = m::mock('ActivatableUser');
        $driver = $this->newDriver($userModel);

        $activationCodeColumn = 'cryptic_act_col';
        $driver->activationCodeColumn = $activationCodeColumn;

        $code = $this->newActivationCode();

        $userModel->shouldReceive('where')
                  ->with($activationCodeColumn, $code)
                  ->andReturn($userModel);

        $userModel->shouldReceive('get')
                  ->andReturn([]);

        $driver->getUserByActivationData(['code'=>$code]);

    }

    /**
     * @expectedException \RuntimeException
     **/
    public function testGetUserByActivationDataThrowsExceptionIfMultipleUsersFound()
    {

        $userModel = m::mock('ActivatableUser');
        $driver = $this->newDriver($userModel);

        $activationCodeColumn = 'cryptic_act_col';
        $driver->activationCodeColumn = $activationCodeColumn;

        $code = $this->newActivationCode();

        $userModel->shouldReceive('where')
                  ->with($activationCodeColumn, $code)
                  ->andReturn($userModel);

        $userModel->shouldReceive('get')
                  ->andReturn([1,2,3]);

        $driver->getUserByActivationData(['code'=>$code]);

    }

    public function testGetUserByActivationDataReturnsFirstFoundUser()
    {

        $userModel = m::mock('ActivatableUser');
        $driver = $this->newDriver($userModel);
        $collection = m::mock('Illuminate\Database\Eloquent\Collection');
        $user = $this->newUserModel(12);

        $activationCodeColumn = 'cryptic_act_col';
        $driver->activationCodeColumn = $activationCodeColumn;

        $code = $this->newActivationCode();

        $userModel->shouldReceive('where')
                  ->with($activationCodeColumn, $code)
                  ->andReturn($userModel);

        $userModel->shouldReceive('get')
                  ->andReturn($collection);

        $collection->shouldReceive('count')
                   ->andReturn(1);

        $collection->shouldReceive('first')
                   ->once()
                   ->andReturn($user);

        $this->assertSame($user, $driver->getUserByActivationData(['code'=>$code]));

    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testActivateWithWrongModelThrowsException()
    {

        $generator = $this->newCodeGenerator();
        $driver = $this->newDriver(null, $generator);
        $user = m::mock('UnActivatableUser');
        $user->exists = true;

        $driver->activate($user);
    }

    public function testActivateWithoutFurtherColumnsSetsOnlyCodeProperty()
    {
        $driver = $this->newDriver();
        $user = m::mock('ActivatableUser');
        $activationCodeColumn = 'cryptic_act_col';
        $isActivatedColumn = '';
        $activationDateColumn = '';

        $originalActivationDateColumn = $driver->activationDateColumn;
        $originalIsActivatedColumn = $driver->isActivatedColumn;

        $driver->activationCodeColumn = $activationCodeColumn;
        $driver->activationDateColumn = $activationDateColumn;
        $driver->isActivatedColumn = $isActivatedColumn;

        $user->shouldReceive('freshTimestamp')
             ->andReturn('timestamp');

        $user->shouldReceive('setAttribute')
             ->with($activationCodeColumn, null)
             ->once();

        $user->shouldReceive('setAttribute')
             ->with($originalIsActivatedColumn, 1)
             ->never();

        $user->shouldReceive('setAttribute')
             ->with($originalActivationDateColumn, 'timestamp')
             ->never();

        $user->shouldReceive('save')->andReturn(true);

        $this->assertTrue($driver->activate($user));


    }

    public function testActivateWithIsActivatedColumnsSetsProperty()
    {
        $driver = $this->newDriver();
        $user = m::mock('ActivatableUser');
        $activationCodeColumn = 'cryptic_act_col';
        $isActivatedColumn = 'cryptic_is_act';
        $activationDateColumn = '';

        $originalActivationDateColumn = $driver->activationDateColumn;

        $driver->activationCodeColumn = $activationCodeColumn;
        $driver->activationDateColumn = $activationDateColumn;
        $driver->isActivatedColumn = $isActivatedColumn;

        $user->shouldReceive('freshTimestamp')
             ->andReturn('timestamp');

        $user->shouldReceive('setAttribute')
             ->with($activationCodeColumn, null)
             ->once();

        $user->shouldReceive('setAttribute')
             ->with($isActivatedColumn, 1)
             ->once();

        $user->shouldReceive('setAttribute')
             ->with($originalActivationDateColumn, 'timestamp')
             ->never();

        $user->shouldReceive('save')->andReturn(true);

        $this->assertTrue($driver->activate($user));

    }

    public function testActivateWitActivationDateColumnSetsProperty()
    {
        $driver = $this->newDriver();
        $user = m::mock('ActivatableUser');
        $activationCodeColumn = 'cryptic_act_col';
        $isActivatedColumn = '';
        $activationDateColumn = 'cryptic_act_date';

        $originalIsActivatedColumn = $driver->isActivatedColumn;

        $driver->activationCodeColumn = $activationCodeColumn;
        $driver->activationDateColumn = $activationDateColumn;
        $driver->isActivatedColumn = $isActivatedColumn;

        $user->shouldReceive('freshTimestamp')
             ->andReturn('timestamp');

        $user->shouldReceive('setAttribute')
             ->with($activationCodeColumn, null)
             ->once();

        $user->shouldReceive('setAttribute')
             ->with($originalIsActivatedColumn, 1)
             ->never();

        $user->shouldReceive('setAttribute')
             ->with($activationDateColumn, 'timestamp')
             ->once();

        $user->shouldReceive('save')->andReturn(true);

        $this->assertTrue($driver->activate($user));

    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testIsActivatedWithWrongModelThrowsException()
    {

        $generator = $this->newCodeGenerator();
        $driver = $this->newDriver(null, $generator);
        $user = m::mock('UnActivatableUser');
        $user->exists = true;

        $driver->isActivated($user);
    }

    public function testIsActivatedWithUnexistingUserReturnsFalse()
    {

        $generator = $this->newCodeGenerator();
        $driver = $this->newDriver(null, $generator);
        $user = m::mock('ActivatableUser');
        $user->exists = false;

        $this->assertFalse($driver->isActivated($user));
    }

    public function testIsActivatedWithoutFurtherColumnsChecksOnlyCode()
    {

        $driver = $this->newDriver();
        $user = m::mock('ActivatableUser');
        $user->exists = true;
        $activationCodeColumn = 'cryptic_act_col';
        $isActivatedColumn = '';
        $activationDateColumn = '';

        $originalActivationDateColumn = $driver->activationDateColumn;
        $originalIsActivatedColumn = $driver->isActivatedColumn;

        $driver->activationCodeColumn = $activationCodeColumn;
        $driver->activationDateColumn = $activationDateColumn;
        $driver->isActivatedColumn = $isActivatedColumn;

        $user->shouldReceive('getAttribute')
             ->with($originalIsActivatedColumn)
             ->never();

        $user->shouldReceive('getAttribute')
             ->with($originalActivationDateColumn)
             ->never();

        $user->shouldReceive('getAttribute')
             ->with($activationCodeColumn)
             ->andReturn(null)
             ->once();

        $this->assertTrue($driver->isActivated($user));

    }

    public function testIsActivatedWithIsActivatedColumnChecksProperty()
    {

        $driver = $this->newDriver();
        $user = m::mock('ActivatableUser');
        $user->exists = true;
        $activationCodeColumn = 'cryptic_act_col';
        $isActivatedColumn = 'cryptic_is_act';
        $activationDateColumn = '';

        $originalActivationDateColumn = $driver->activationDateColumn;
        $originalIsActivatedColumn = $driver->isActivatedColumn;

        $driver->activationCodeColumn = $activationCodeColumn;
        $driver->activationDateColumn = $activationDateColumn;
        $driver->isActivatedColumn = $isActivatedColumn;

        $user->shouldReceive('getAttribute')
             ->with($isActivatedColumn)
             ->andReturn(1)
             ->once();

        $user->shouldReceive('getAttribute')
             ->with($originalActivationDateColumn)
             ->never();

        $user->shouldReceive('getAttribute')
             ->with($activationCodeColumn)
             ->never();

        $this->assertTrue($driver->isActivated($user));

        $user->shouldReceive('getAttribute')
             ->with($isActivatedColumn)
             ->andReturn(0)
             ->once();

        $this->assertFalse($driver->isActivated($user));

    }

    public function testIsActivatedWithOnlyActivatedAtChecksProperty()
    {

        $driver = $this->newDriver();
        $user = m::mock('ActivatableUser');
        $user->exists = true;
        $activationCodeColumn = 'cryptic_act_col';
        $isActivatedColumn = '';
        $activationDateColumn = 'cryptic_act_date';

        $originalIsActivatedColumn = $driver->isActivatedColumn;

        $driver->activationCodeColumn = $activationCodeColumn;
        $driver->activationDateColumn = $activationDateColumn;
        $driver->isActivatedColumn = $isActivatedColumn;

        $user->shouldReceive('getAttribute')
             ->with($originalIsActivatedColumn)
             ->never();

        $user->shouldReceive('getAttribute')
             ->with($activationCodeColumn)
             ->never();

        $user->shouldReceive('getAttribute')
             ->with($activationDateColumn)
             ->once()
             ->andReturn(new DateTime);

        $this->assertTrue($driver->isActivated($user));

        // Check for null values
        $user->shouldReceive('getAttribute')
             ->with($activationDateColumn)
             ->once()
             ->andReturn(null);

        $this->assertFalse($driver->isActivated($user));

        // Check for non-datetime values
        $user->shouldReceive('getAttribute')
             ->with($activationDateColumn)
             ->once()
             ->andReturn('2015-01-01 15:05:59');

        $this->assertTrue($driver->isActivated($user));

    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testGetActivationDataWithWrongModelThrowsException()
    {

        $generator = $this->newCodeGenerator();
        $driver = $this->newDriver(null, $generator);
        $user = m::mock('UnActivatableUser');
        $user->exists = true;

        $driver->getActivationData($user);
    }

    public function testGetActivationDataPassesUserValues()
    {

        $driver = $this->newDriver();
        $user = m::mock('ActivatableUser');
        $user->exists = true;
        $activationCodeColumn = 'cryptic_act_col';
        $code = $this->newActivationCode();

        $shouldBeData = ['code' => $code];

        $driver->activationCodeColumn = $activationCodeColumn;
        $user->shouldReceive('getAttribute')
             ->with($activationCodeColumn)
             ->once()
             ->andReturn($code);

        $this->assertEquals($shouldBeData, $driver->getActivationData($user));

    }

    public function newDriver($userModel=null,$generator=null)
    {
        $userModel = $userModel ?: $this->newUserModel();
        $generator = $generator ?: $this->newCodeGenerator();

        return new UserModelDriver($userModel, $generator);
    }

    public function newUserModel($id=1)
    {
        $user = new ActivatableUser;
        $user->id = $id;
        return $user;
    }

    public function newCodeGenerator()
    {
        return m::mock('Permit\Random\GeneratorInterface');
    }

    public function newActivationCode($length=42)
    {
        return str_repeat('X', $length);
    }

    public function tearDown()
    {
        m::close();
    }

}

class ActivatableUser extends Model implements UserInterface{

    use EloquentUserTrait;

}

class UnActivatableUser extends Model implements UserInterface{

    use EloquentUserTrait;

}