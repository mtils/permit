<?php namespace Permit\Support\Laravel\Groups;

use Permit\Groups\GroupInterface;

trait EloquentHolderTrait{


    /**
     * {@inheritdoc}
     *
     * @return \Traversable
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function attachGroup(GroupInterface $group)
    {
        if($this->isInGroup($group)){
            return true;
        }

        $this->groups()->attach($group);

        return true;
    }

    /**
     * @brief Detaches group $group from holder
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function detachGroup(GroupInterface $group)
    {

        if(!$this->isInGroup($group)){
            return true;
        }

        $this->groups()->detach($group);

        return true;

    }

    /**
     * Returns if this holder is attached to group $group
     *
     * @param  \Permit\Groups\GroupInterface  $group
     * @return bool
     */
    public function isInGroup(GroupInterface $group)
    {

        foreach ($this->getGroups() as $attachedGroup) {
            if ($attachedGroup->getGroupId() == $group->getGroupId()) {
                return true;
            }
        }

        return false;

    }

    public function groups()
    {
        return $this->belongsToMany(
            static::getGroupModelClass(),
            static::getGroupPivotTable()
        );
    }

    /**
     * @see \Permit\Permission\Holder\NestedHolderInterface
     * @return \Traversable<\Permit\Permission\Holder\HolderInterface>
     **/
    public function getSubHolders()
    {
        return $this->groups;
    }

    /**
     * Returns the classname of the related group model
     * Just define a static::$groupModelClass property to set it
     *
     * @return string
     **/
    public static function getGroupModelClass()
    {

        return property_exists(
            get_called_class(),
            'groupModelClass'
        ) ? static::$groupModelClass : 'App\User';

    }

   /**
    * Returns the pivot table name of its group relation
    * Just define a static::$groupsPivotTable property to set it
    *
    * @return string
    **/
    public static function getGroupPivotTable()
    {

        return property_exists(
            get_called_class(),
            'groupsPivotTable'
        ) ? static::$groupsPivotTable : 'users_groups';
    }

}