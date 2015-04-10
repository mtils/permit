<?php namespace Permit\Support\Laravel\Groups;

use Illuminate\Database\Eloquent\Model;

use Permit\Support\Laravel\Permission\Holder\EloquentJsonPermissionsTrait;
use Permit\Permission\Holder\HolderInterface;
use Permit\Groups\GroupInterface;

class EloquentGroup extends Model implements HolderInterface, GroupInterface
{

    use EloquentJsonPermissionsTrait;

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getGroupId(){
        return $this->getKey();
    }

}