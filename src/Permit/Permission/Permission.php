<?php namespace Permit\Permission;

class Permission implements PermissionInterface{

    protected $code = '';

    protected $title = '';

    protected $description = '';

    protected $category;

    protected $categoryId;

    public function getCode(){
        return $this->code;
    }

    public function setCode($code){
        $this->code = $code;
        return $this;
    }

    public function getTitle(){
        if(!$this->title){
            return $this->code;
        }
        return $this->title;
    }

    public function setTitle($title){
        $this->title = $title;
        return $this->title;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setDescription($description){
        $this->description = $description;
        return $this;
    }

    public function getCategory(){
        return $this->category;
    }

    public function setCategory(CategoryInterface $category){
        $this->category = $category;
        return $this;
    }

    public function getCategoryId(){
        return $this->categoryId;
    }

}