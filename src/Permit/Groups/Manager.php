<?php namespace Permit\Groups;

use Permit\Access\CheckerInterface;

class Manager implements ManagerInterface{

    protected $groupRepo;

    protected $checker;

    public function __construct(GroupRepositoryInterface $groupRepo, CheckerInterface $checker){

        $this->groupRepo = $groupRepo;
        $this->checker = $checker;

    }

    /**
     * @brief Returns all groups the user $holder can access
     *
     * @return \Traversable Set of groups
     **/
    public function findAccessableGroupsFor(HolderInterface $holder){

        $accessableGroups = [];

        foreach($this->groupRepo->all() as $group){
            if($this->checker->hasAccess($holder, $group)){
               $accessableGroups[] = $group;
            }
        }

        return $accessableGroups;

    }

    /**
     * @brief Returns group with id $id
     *
     * @param mixed $id
     * @return \Permit\Groups\GroupInterface;
     **/
    public function findByGroupId($id){
        return $this->groupRepo->findByGroupId($id);
    }

    /**
     * @brief Returns a new group filled with attributes $attributes
     *        Does NOT save the group
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function getNew(array $attributes=[]){
        return $this->groupRepo->getNew($attributes);
    }

    /**
     * @brief Returns a new group filled with attributes $attributes
     *        SAVES the group
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function create(array $attributes=[]){
        return $this->groupRepo->create($attributes);
    }

    /**
     * @brief Saves the group
     *
     * @param Permit\Groups\GroupInterface $group
     * @return bool
     **/
    public function save(GroupInterface $group){
        return $this->groupRepo->save($group);
    }

    /**
     * @brief Returns all groups
     *
     * @return \Traversable Set of groups
     **/
    public function all(){
        return $this->groupRepo->all();
    }

}