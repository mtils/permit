<?php namespace Permit\Permission;

use Permit\Permission\Holder\HolderInterface as Holder;
use Permit\Permission\Holder\NestedHolderInterface as NestedHolder;

class NoWildcardMerger implements MergerInterface
{

    /**
     * {@inheritdoc}
     *
     * @param \Permit\Permission\Holder\HolderInterface $holder
     * @return array
     **/
    public function getMergedPermissions(Holder $holder)
    {
        return $this->toBoolPermissions($this->collectHolderCodes($holder));
    }

    public function collectHolderCodes(Holder $holder)
    {
        $holderCodes = [];

        foreach ($holder->permissionCodes() as $code) {

            $access = $holder->getPermissionAccess($code);

            $holderCodes[$code] = $access;

        }

        if (!$holder instanceof NestedHolder) {
            return $holderCodes;
        }

        return $this->mergeNested($holderCodes, $this->mergeSubHolderCodes($holder));

    }

    public function mergeSubHolderCodes(NestedHolder $holder)
    {

        $foundedCodes = [];

        foreach ($holder->getSubHolders() as $subHolder) {

            $merged = $this->collectHolderCodes($subHolder);

            foreach ($merged as $code=>$access) {
                $foundedCodes[$code] = $this->resolveAccess($code, $access, $foundedCodes);
            }

        }

        return $foundedCodes;

    }

    public function mergeNested(array $holderCodes, array $subHolderCodes)
    {

        $merged = [];

        $allCodes = array_unique(
            array_merge(
                array_keys($holderCodes),
                array_keys($subHolderCodes)
            )
        );

        foreach ($allCodes as $code) {

            if (isset($holderCodes[$code]) && $holderCodes[$code] != Holder::INHERITED) {
                $merged[$code] = $holderCodes[$code];
                continue;
            }

            if (isset($subHolderCodes[$code]) && $subHolderCodes[$code] != Holder::INHERITED) {
                $merged[$code] = $subHolderCodes[$code];
            }

        }

        return $merged;

    }

    public function toBoolPermissions(array $permissions)
    {

        $boolPermissions = [];

        foreach ($permissions as $code=>$access) {

            if ($access === Holder::INHERITED) {
                continue;
            }

            $boolPermissions[$code] = ($access === Holder::GRANTED) ? true : false;

        }

        return $boolPermissions;

    }

    protected function resolveAccess($code, $access, &$foundedCodes)
    {

        if (!isset($foundedCodes[$code])) {
            return $access;
        }

        if ($foundedCodes[$code] === Holder::DENIED) {
            return $foundedCodes[$code];
        }

        if ($foundedCodes[$code] === Holder::GRANTED) {
            return $access === Holder::DENIED ? $access : Holder::GRANTED;
        }

        return $access;

    }

}