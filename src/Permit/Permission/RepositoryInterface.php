<?php namespace Permit\Permission;

use Permit\Holder\HolderInterface;

/**
 * @brief In Permit a Permission is just cosmetic for your CMS
 *        or some other GUI. A Permission describes what the permission-code
 *        mean.
 **/
interface RepositoryInterface{

    /**
     * @brief Add a permission code
     *
     * @return void
     **/
    public function addCode($code);

    /**
     * @brief Checks if a code exists
     *
     * @return bool
     **/
    public function codeExists($code);

    /**
     * @brief Returns a Permission with code $code
     *
     * @param string $code The permission code
     * @return Permit\Permission
     **/
    public function get($code);

    /**
     * @brief Return the category with id $id
     *
     * @param string $id
     **/
    public function getCategory($id);

    /**
     * @brief Returns all permissions
     *
     * @return \Traversable
     **/
    public function all();

    /**
     * @brief Returns a filtered Traversable of permissions
     *
     * @param Permit\Holder\HolderInterface $holder (optional)
     * @param Permit\Permission\CategoryInterface $category
     * @return Traversable
     **/
    public function filtered(HolderInterface $holder=NULL, CategoryInterface $category=NULL);

    /**
     * @brief Return all permissions of $holder
     *
     * @param Permit\Holder\HolderInterface $holder (optional)
     * @return Traversable of categories
     **/
    public function categories(HolderInterface $holder=NULL);

}