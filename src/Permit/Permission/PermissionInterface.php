<?php namespace Permit\Permission;

interface PermissionInterface{

    public function getCode();

    public function setCode($code);

    public function getName();

    public function setName($name);

    public function getTitle();

    public function setTitle($title);

    public function getDescription();

    public function setDescription($description);

    public function getCategory();

    public function setCategory(CategoryInterface $category);

    public function getCategoryId();

}