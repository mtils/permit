<?php namespace Permit\Support\Sentry\Groups;

use Permit\Groups\ProviderInterface;
use Permit\Groups\GroupRepositoryInterface;

use Cartalyst\Sentry\Groups\ProviderInterface as SentryProviderInterface;

class ProviderRepository implements ProviderInterface, GroupRepositoryInterface{

    protected $sentryProvider;

    public function __construct(SentryProviderInterface $sentryProvider){

        $this->sentryProvider = $sentryProvider;

    }

    /**
     * @brief Returns group with id $id
     *
     * @param int $id
     * @return \Permit\Groups\GroupInterface;
     **/
    public function findByGroupId($id){
        return $this->sentryProvider->findById($id);
    }

    /**
     * @brief Returns a new group filled with attributes $attributes
     *        Does NOT save the group
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function getNew(array $attributes=[]){

        $model = $this->sentryProvider->createModel();

        if($attributes){
            $model->fill($attributes);
        }

        return $model;

    }

    /**
     * @brief Returns a new group filled with attributes $attributes
     *        SAVES the group
     *
     * @param array $attributes The attributes to prefill the group
     * @return Permit\Groups\GroupInterface
     **/
    public function create(array $attributes=[]){
        return $this->sentryProvider->create($attributes);
    }

    /**
     * @brief Saves the group
     *
     * @param Permit\Groups\GroupInterface $group
     * @return bool
     **/
    public function save(GroupInterface $group){
        $group->save();
    }

    /**
     * @brief Returns all groups
     *
     * @return \Traversable Set of groups
     **/
    public function all(){
        return $this->sentryProvider->findAll();
    }

}