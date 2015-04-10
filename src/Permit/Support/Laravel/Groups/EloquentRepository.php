<?php namespace Permit\Support\Laravel\Groups;

use Permit\Groups\GroupRepositoryInterface;
use Permit\Groups\GroupInterface;
use Illuminate\Database\Eloquent\Model;

class EloquentRepository implements GroupRepositoryInterface{

    /**
     * The eloquent model (as a prototype)
     **/
    protected $groupModel;

    public function __construct(Model $groupModel)
    {
        $this->groupModel = $groupModel;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $id
     * @return \Permit\Groups\GroupInterface;
     **/
    public function findByGroupId($id)
    {
        return $this->groupModel->find($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function getNew(array $attributes=[])
    {
        return $this->groupModel->newInstance($attributes);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function create(array $attributes=[])
    {
        $group = $this->getNew($attributes);
        $group->save();
        return $group;
    }

    /**
     * {@inheritdoc}
     *
     * @param Permit\Groups\GroupInterface $group
     * @return bool
     **/
    public function save(GroupInterface $group)
    {
        return $group->save();
    }

    /**
     * @brief Returns all groups
     *
     * @return \Traversable Set of groups
     **/
    public function all()
    {
        return $this->groupModel->all();
    }

}