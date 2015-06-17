<?php namespace Permit\Permission;

use Permit\Permission\Holder\HolderInterface;

interface MergerInterface
{

    /**
     * Returns the merged permissions of $holder.
     * The merged permissions has to have the following structure:
     *
     * [
     *     'cms.access'   => true
     *     'pages.delete' => true
     * ]
     *
     * The array should only contain granted permissions.
     * The access is granted if the key exists and its value is true
     * The access is denied if the key exists and its value is false
     * The access is unknown if the key does not exist, which means
     * basically denied if not any other access checker returns false
     * when asking for hasAccess()
     *
     * @param \Permit\Permission\Holder\HolderInterface $holder
     * @return array
     **/
    public function getMergedPermissions(HolderInterface $holder);

}