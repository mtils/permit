<?php namespace Permit\Permission;

class Category implements CategoryInterface{

    protected $id;

    protected $title;

    protected $permissions = [];

    /**
     * @brief Return the id of this category
     *
     * @return string
     **/
    public function getId(){
        return $this->id;
    }

    /**
     * @brief Set the id of this category
     *
     * @param string $id
     **/
    public function setId($id){
        $this->id = $id;
        return $this;
    }

    /**
     * @brief Return the title of this category
     *
     * @return string
     **/
    public function getTitle(){
        if(!$this->title){
            return $this->id;
        }
        return $this->title;
    }

    /**
     * @brief Set the title ob this category
     *
     * @param string $title
     * @return void
     **/
    public function setTitle($title){
        $this->title = $title;
        return $this;
    }

    /**
     * @brief Return all Permissions of this category
     *
     * @return Traversable of Permission objects
     **/
    public function getPermissions(){
        return $this->permissions;
    }

    /**
     * @brief Set the permissions of this category
     *
     * @param array $permissions
     * @return void
     **/
    public function setPermissions(array $permissions){
        $this->permissions = $permissions;
        return $this;
    }

}