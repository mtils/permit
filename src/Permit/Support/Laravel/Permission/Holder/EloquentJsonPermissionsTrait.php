<?php namespace Permit\Support\Laravel\Permission\Holder;


use InvalidArgumentException;
use UnexpectedValueException;

use Permit\Permission\Holder\HolderInterface;

trait EloquentJsonPermissionsTrait{

    /**
     * {@inheritdoc}
     *
     * @param string $code
     * @return int
     **/
    public function getPermissionAccess($code)
    {
        $permissions = $this->permissions;

        if(isset($permissions[$code])){
            return $permissions[$code];
        }

        return HolderInterface::INHERITED;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $code The permission code
     * @param int $access self::GRANTED|self::UNAPPROVED|self::DENIED
     * @return void
     **/
    public function setPermissionAccess($code, $access)
    {

        $permissions = $this->permissions;
        $permissions[$code] = $access;
        $this->permissions = $permissions;

    }

    /**
     * {@inheritdoc}
     *
     * @return array
     **/
    public function permissionCodes()
    {
        return array_keys($this->permissions);
    }

    /**
     * Mutator to get all permissions directly setted to this holder.
     *
     * @param  mixed $permissions (string|array)
     * @return array
     */
    public function getPermissionsAttribute($permissions)
    {
        if (!$permissions) {
            return [];
        }

        if (is_array($permissions)) {
            return $permissions;
        }

        if (!$permissionArray = json_decode($permissions, true)) {
            throw new InvalidArgumentException("Cannot JSON decode permissions [$permissions].");
        }

        return $permissionArray;
    }

    /**
     * Mutator for taking permissions.
     *
     * @param  array  $permissions
     * @return string
     */
    public function setPermissionsAttribute(array $permissions)
    {

        // Loop through and adjust permissions as needed
        foreach ($permissions as $permission => &$value) {

            $this->checkValidAccess($value);

            // If the value is 0, delete it
            if ($value === 0) {
                unset($permissions[$permission]);
            }

        }

        $this->attributes['permissions'] = 
            count($permissions) ? json_encode($permissions) : '';
    }

    /**
     * Checks if a access definition is a valid access key
     *
     * @param int
     **/
    protected function checkValidAccess($accessValue)
    {
        $allowedValues = [
            HolderInterface::GRANTED,
            HolderInterface::INHERITED,
            HolderInterface::DENIED
        ];

        if(!in_array($accessValue, $allowedValues)){
            throw new UnexpectedValueException("$accessValue is not a valid access key");
        }
    }

}