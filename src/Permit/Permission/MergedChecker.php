<?php namespace Permit\Permission;

use Permit\User\UserInterface;
use Permit\Access\CheckerInterface;
use Permit\Permission\Holder\HolderInterface as Holder;
use UnexpectedValueException;

class MergedChecker implements CheckerInterface
{

    public $superUserPermission = 'superuser';

    /**
     * @var \Permit\Permission\MergerInterface
     **/
    protected $merger;


    /**
     * @param \Permit\Permission\MergerInterface $merger
     **/
    public function __construct(MergerInterface $merger)
    {
        $this->merger = $merger;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='default')
    {
        return $this->hasPermissionAccess($user, $resource, $context);
    }

    /**
     * Returns if holder has access to $resourceOrCode within $context
     * The order is as follows:
     *
     * 1. Does the holder itself has a permission GRANTED|ALLOWED
     * 2. Does the holder itself has a fuzzy (permission.*) permission GRANTED|ALLOWED
     * 3. Does the subholders have a permission GRANTED|ALLOWED
     * 4. Does the subholders have a fuzzy permission GRANTED|ALLOWED
     *
     * If many of the subholders have the same permission with different
     * access, any DENIED will win against GRANTED or INHERITED.
     * GRANTED will win against INHERITED
     *
     * If many permissions are passed (via PermissionableInterface or array)
     * the access will only be granted if the holder has all permissions
     *
     * @param \Permit\Permission\Holder\HolderInterface $holder The Holder of permission codes
     * @param string|\Permit\Permission\PermissionableInterface|array $resource The resource
     * @param int $context (optional)
     * @return bool|null
     **/
    public function hasPermissionAccess(Holder $holder, $resourceOrCode, $context=PermissionableInterface::ACCESS)
    {

         if ($holder->isSystem()) {
             return true;
         }

         if ($resourceOrCode != $this->superUserPermission) {
            if ($this->hasPermissionAccess($holder, $this->superUserPermission)){
                return true;
            }
         }

        if (!$codes = $this->resourceToCodes($resourceOrCode, $context)) {
            return;
        }

        return $this->hasAccessToAllCodes($holder, $codes);

    }

    protected function hasAccessToAllCodes(Holder $holder, array $codes)
    {

        $access = [];
        $permissions = $this->merger->getMergedPermissions($holder);

        foreach ($codes as $code) {

            // If one inherited access found, return null
            if (!isset($permissions[$code])) {
                return;
            }

            // If one inherited access found, return null
            if ($permissions[$code] === null) {
                return;
            }

            // If one denied access found, return false
            if ($permissions[$code] === false) {
                return false;
            }

            // This assures that all remaining cases are GRANTED
            if ($permissions[$code] !== true) {
                throw new UnexpectedValueException("Unexpected value {$permissions[$code]} for access");
            }

        }

        return true;

    }

    protected function resourceToCodes($resource, $context)
    {

        if ($resource instanceof PermissionableInterface) {
            return $resource->requiredPermissionCodes($context);
        }

        if (is_array($resource)) {
            return $resource;
        }

        if (is_string($resource)) {
            return [$resource];
        }

    }

}
