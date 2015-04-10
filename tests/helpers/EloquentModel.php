<?php

class EloquentModel
{

    protected $attributes = [];

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function getAttribute($key)
    {

        $value = $this->getAttributeFromArray($key);
        $mutatorName = 'get'.$this->getAttributeMutatorName($key);

        if(method_exists($this, $mutatorName)){
            return $this->{$mutatorName}($value);
        }

        $relationName = $this->getRelationMethodName($key);

        if(method_exists($this, $relationName)){
            return $this->{$relationName}()->getResults();
        }

        return $value;

    }

    public function setAttribute($key, $value)
    {

        $mutatorName = 'set'.$this->getAttributeMutatorName($key);

        if(method_exists($this, $mutatorName)){
            return $this->{$mutatorName}($value);
        }

        $this->attributes[$key] = $value;

    }

    public function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    protected function getAttributeMutatorName($key)
    {
        return $this->studly($key).'Attribute';
    }

    protected function getRelationMethodName($key){
        return lcfirst($this->studly($key));
    }

    protected function studly($value){
        return ucwords(str_replace(array('-', '_'), ' ', $value));
    }

}