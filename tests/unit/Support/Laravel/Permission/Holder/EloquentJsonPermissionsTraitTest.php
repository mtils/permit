<?php 

use Permit\Permission\Holder\HolderInterface;
use Permit\Support\Laravel\Permission\Holder\EloquentJsonPermissionsTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../../../../helpers/EloquentModel.php';


class EloquentJsonPermissionsTraitTest extends TestCase
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Permit\Permission\Holder\HolderInterface',
            $this->newUser()
        );
    }

    public function testGetUnknownPermission()
    {

        $user = $this->newUser();

        $this->assertSame(
            $user->getPermissionAccess('cms.access'),
            HolderInterface::INHERITED
        );

    }

    public function testGetDeniedPermission()
    {

        $user = $this->newUser();

        $user->setPermissionAccess('cms.access', HolderInterface::DENIED);

        $this->assertSame(
            $user->getPermissionAccess('cms.access'),
            HolderInterface::DENIED
        );

    }

    public function testGetGrantedPermission()
    {

        $user = $this->newUser();

        $user->setPermissionAccess('cms.access', HolderInterface::GRANTED);

        $this->assertSame(
            $user->getPermissionAccess('cms.access'),
            HolderInterface::GRANTED
        );

    }

    public function testGetPermissionCodesReturnsAllSetted()
    {

        $user = $this->newUser();
        $code1 = 'cms.access';
        $code2 = 'users.activate';
        $code3 = 'newsletter.send';

        $user->setPermissionAccess($code1, HolderInterface::GRANTED);
        $user->setPermissionAccess($code2, HolderInterface::DENIED);
        $user->setPermissionAccess($code3, HolderInterface::GRANTED);

        $this->assertEquals([$code1, $code2, $code3], $user->permissionCodes());

    }

    public function testGetPermissionsAttributeReturnsArrayIfArrayPassed()
    {

        $user = $this->newUser();
        $permissions = ['cms.access' => HolderInterface::DENIED];

        $this->assertEquals(
            $permissions,
            $user->getPermissionsAttribute($permissions)
        );

    }

    /**
     * @expectedException \InvalidArgumentException
     **/
    public function testGetPermissionsAttributeWithInvalidJsonThrowsException()
    {
        $user = $this->newUser();
        $permissions = ']invalidJSON[';

        $user->getPermissionsAttribute($permissions);
    }

    public function testSetInheritedPermissionRemovesPermission()
    {

        $user = $this->newUser();

        $code1 = 'cms.access';
        $code2 = 'users.activate';
        $code3 = 'newsletter.send';

        $user->setPermissionAccess($code1, HolderInterface::GRANTED);
        $user->setPermissionAccess($code2, HolderInterface::DENIED);
        $user->setPermissionAccess($code3, HolderInterface::GRANTED);

        $user->setPermissionAccess($code2, HolderInterface::INHERITED);

        $this->assertEquals([$code1, $code3], $user->permissionCodes());

    }

    public function testSetPermissionsProducesValidJson()
    {

        $user = $this->newUser();

        $code1 = 'cms.access';
        $code2 = 'users.activate';
        $code3 = 'newsletter.send';

        $user->setPermissionAccess($code1, HolderInterface::GRANTED);
        $user->setPermissionAccess($code2, HolderInterface::DENIED);
        $user->setPermissionAccess($code3, HolderInterface::GRANTED);

        $jsonified = $user->getAttributeFromArray('permissions');

        $result = [];

        foreach ($user->permissionCodes() as $code){
            $result[$code] = $user->getPermissionAccess($code);
        }

        $this->assertEquals(json_decode($jsonified,true), $result);

    }

    /**
     * @expectedException UnexpectedValueException
     **/
    public function testSetPermissionsAttributeWithInvalidAccessThrowsException()
    {

        $user = $this->newUser();

        $code1 = 'cms.access';
        $code2 = 'users.activate';
        $code3 = 'newsletter.send';

        $user->setPermissionAccess($code1, HolderInterface::GRANTED);
        $user->setPermissionAccess($code2, 300);
        $user->setPermissionAccess($code3, HolderInterface::GRANTED);

        $jsonified = $user->getAttributeFromArray('permissions');

        $result = [];

        foreach ($user->permissionCodes() as $code){
            $result[$code] = $user->getPermissionAccess($code);
        }

        $this->assertEquals(json_decode($jsonified,true), $result);

    }

    protected function newUser(){
        return new User;
    }

}

class User extends EloquentModel implements HolderInterface{
    use EloquentJsonPermissionsTrait;
}
