<?php namespace Permit\Groups;

interface GroupRepositoryInterface{

    /**
     * @brief Returns group with id $id
     *
     * @param mixed $id
     * @return \Permit\Groups\GroupInterface;
     **/
    public function findByGroupId($id);

    /**
     * @brief Returns a new group filled with attributes $attributes
     *        Does NOT save the group
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function getNew(array $attributes=[]);

    /**
     * @brief Returns a new group filled with attributes $attributes
     *        SAVES the group
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function create(array $attributes=[]);

    /**
     * @brief Saves the group
     *
     * @param Permit\Groups\GroupInterface $group
     * @return bool
     **/
    public function save(GroupInterface $group);

    /**
     * @brief Returns all groups
     *
     * @return \Traversable Set of groups
     **/
    public function all();

}