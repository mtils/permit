<?php namespace Permit\Permission;

use Permit\User\UserInterface;
use Permit\Groups\GroupInterface;
use Permit\Permission\Holder\HolderInterface as Holder;
use Permit\Permission\Holder\NestedHolderInterface as NestedHolder;

class CachedMerger implements MergerInterface
{

    /**
     * @var \Permit\Permission\MergerInterface
     **/
    protected $merger;

    /**
     * @var array
     **/
    protected $mergedCache = [];

    public function __construct(MergerInterface $merger)
    {
        $this->merger = $merger;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\Permission\Holder\HolderInterface $holder
     * @return array
     **/
    public function getMergedPermissions(Holder $holder)
    {

        $cacheId = $this->getCacheId($holder);

        if (isset($this->mergedCache[$cacheId])) {
            return $this->mergedCache[$cacheId];
        }

        $this->mergedCache[$cacheId] = $this->merger->getMergedPermissions($holder);

        return $this->mergedCache[$cacheId];

    }

    protected function getCacheId(Holder $holder)
    {
        if($holder instanceof UserInterface)
        {
            return "user|".$holder->getAuthId();
        }

        if ($holder instanceof GroupInterface) {
            return "group|".$holder->getGroupId();
        }

        return spl_object_hash($holder);
    }

}