<?php namespace Permit\Support\Laravel\Groups;

use Illuminate\Database\Eloquent\Model;

use Permit\Support\Laravel\Permission\Holder\EloquentJsonPermissionsTrait;
use Permit\Permission\Holder\HolderInterface;
use Permit\Groups\GroupInterface;
use Permit\Permission\PermissionableInterface;
use Permit\Permission\Holder\PermissionableHolderTrait;

class EloquentGroup extends Model implements HolderInterface, GroupInterface, PermissionableInterface
{

    use EloquentJsonPermissionsTrait;
    use PermissionableHolderTrait;

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getGroupId(){
        return $this->getKey();
    }

}