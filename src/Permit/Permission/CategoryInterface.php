<?php namespace Permit\Permission;

interface CategoryInterface{

    /**
     * @brief Return the id of this category
     *
     * @return string
     **/
    public function getId();

    /**
     * @brief Set the id of this category
     *
     * @param string $id
     **/
    public function setId($id);

    /**
     * @brief Return the title of this category
     *
     * @return string
     **/
    public function getTitle();

    /**
     * @brief Set the title ob this category
     *
     * @param string $title
     * @return void
     **/
    public function setTitle($title);

    /**
     * @brief Return all Permissions of this category
     *
     * @return Traversable of Permission objects
     **/
    public function getPermissions();

    /**
     * @brief Set the permissions of this category
     *
     * @param array $permissions
     * @return void
     **/
    public function setPermissions(array $permissions);

}