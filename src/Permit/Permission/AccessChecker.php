<?php namespace Permit\Permission;


use Permit\Access\CheckerInterface;
use Permit\User\UserInterface;
use Permit\Permission\Holder\HolderInterface;
use Permit\Permission\Holder\NestedHolderInterface;
use Permit\Permission\PermissionableInterface;

use InvalidArgumentException;

class AccessChecker implements CheckerInterface
{

    public $superUserPermission = 'superuser';

    protected $permissionCache = [];

    /**
     * @brief Returns if user has access to $resource within $context
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='default')
    {

        return $this->hasPermissionAccess($user, $resource, $context);

    }

    /**
     * Returns if holder has access to $resourceOrCode within $context
     * The order is as follows:
     *
     * 1. Does the holder itself has a permission GRANTED|ALLOWED
     * 2. Does the holder itself has a fuzzy (permission.*) permission GRANTED|ALLOWED
     * 3. Does the subholders have a permission GRANTED|ALLOWED
     * 4. Does the subholders have a fuzzy permission GRANTED|ALLOWED
     *
     * If many of the subholders have the same permission with different
     * access, any DENIED will win against GRANTED or INHERITED.
     * GRANTED will win against INHERITED
     *
     * If many permissions are passed (via PermissionableInterface or array)
     * the access will only be granted if the holder has all permissions
     *
     * @param \Permit\Permission\Holder\HolderInterface $holder The Holder of permission codes
     * @param string|\Permit\Permission\PermissionableInterface|array $resource The resource
     * @param int $context (optional)
     * @return bool|null
     **/
    public function hasPermissionAccess(HolderInterface $holder, $resourceOrCode, $context=PermissionableInterface::ACCESS)
    {

         if ($holder->isSystem())
         {
             return true;
         }

         if ($resourceOrCode != $this->superUserPermission) {
            if ($this->hasPermissionAccess($holder, $this->superUserPermission)){
                return true;
            }
         }

        if($resourceOrCode instanceof PermissionableInterface){
            $codes = $resourceOrCode->requiredPermissionCodes($context);
        }
        elseif(is_array($resourceOrCode)){
            $codes = $resourceOrCode;
        }
        elseif(is_string($resourceOrCode)){
            $codes = [$resourceOrCode];
        }
        else{
            return null;
        }

        $holderGranted = false;

        foreach($codes as $code){

            $access = $this->getPermissionCodeAccess($holder, $code);

            if($access == HolderInterface::GRANTED){
                $holderGranted = true;
            }

            if($access == HolderInterface::DENIED){
                return false;
            }

        }

        if($holderGranted){
            return true;
        }

        if(!$holder instanceof NestedHolderInterface){
            return null;
        }

        $subHoldersGranted = false;
        $subHolders = $holder->getSubHolders();

        foreach($codes as $code){

            $access = $this->getMergedSubHoldersAccess($subHolders, $code);

            if($access == HolderInterface::GRANTED){
                $subHoldersGranted = true;
            }

            if($access == HolderInterface::DENIED){
                return false;
            }

        }

        return $subHoldersGranted ? true : null;

    }

    protected function checkForHolderAccess(HolderInterface $holder, array $codes)
    {

        $cacheId = $this->getCacheId($holder, $codes);

        if (isset($this->cache[$cacheId])) {
            return $this->cache[$cacheId];
        }

        $granted = false;

        foreach($codes as $code){

            $access = $this->getPermissionCodeAccess($holder, $code);

            if($access == HolderInterface::GRANTED){
                $granted = true;
            }

            if($access == HolderInterface::DENIED){
                $granted = false;
                break;
            }

        }

        $this->cache[$cacheId] = $granted;

        return $granted;

    }

    /**
     * Extracts the permission code access off an holder. First tries direct
     * access ("cms.access") then fuzzy access ("cms.*")
     *
     * @param \Permit\Permission\Holder\HolderInterface $holder
     * @param string $code
     * @return int (HolderInterface::INHERITED,...)
     **/
    public function getPermissionCodeAccess(HolderInterface $holder, $code)
    {

        $holderAccess = $holder->getPermissionAccess($code);

        if($holderAccess === HolderInterface::DENIED ||
           $holderAccess === HolderInterface::GRANTED){

            return $holderAccess;

        }

        if($fuzzyCode = $this->getFuzzyCode($code)){
            return $holder->getPermissionAccess($fuzzyCode);
        }

        return HolderInterface::INHERITED;

    }

    /**
     * Extracts the permission code access off an array of holders.
     * DENY wins against GRANTED, GRANTED wins against INHERITED.
     * This method first checks direct access, then fuzzy access on each
     * holder (see getPermissionCodeAccess())
     *
     * @param \Permit\Permission\Holder\HolderInterface $holder
     * @param string $code
     * @return int (HolderInterface::INHERITED,...)
     **/
    public function getMergedSubHoldersAccess($subHolders, $code)
    {

        $granted = HolderInterface::INHERITED;

        foreach($subHolders as $subHolder){

            $access = $this->getPermissionCodeAccess($subHolder, $code);

            if($access === HolderInterface::DENIED){
                return $access;
            }

            if($access === HolderInterface::GRANTED){
                $granted = $access;
            }

        }

        return $granted;

    }

    /**
     * Builds a fuzzy code representation of a code. ('cms.access'=>'cms.*')
     *
     * @param string $code
     * @return string
     **/
    public function getFuzzyCode($code)
    {

        $codeParts = explode('.',$code);

        if( count($codeParts) > 1){
            array_pop($codeParts);
            $prefix = implode('.',$codeParts);
            return "$prefix.*";
        }

        return '';
    }

    protected function getCacheId($groupOrUser, array $codes)
    {
        if($groupOrUser instanceof UserInterface)
        {
            return "group|".$groupOrUser->getAuthId()."|".implode('|',$codes);
        }
        return "user-".$groupOrUser->getGroupId()."|".implode('|',$codes);
    }

}