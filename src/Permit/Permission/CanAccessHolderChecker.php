<?php namespace Permit\Permission;

use Permit\User\UserInterface;
use Permit\Access\CheckerInterface;
use Permit\Permission\Holder\HolderInterface as Holder;
use UnexpectedValueException;

/**
 * This checker if user A can access user B
 **/
class CanAccessHolderChecker implements CheckerInterface
{

    /**
     * @var \Permit\Permission\MergerInterface
     **/
    protected $merger;

    /**
     * @var \Permit\Access\CheckerInterface
     **/
    protected $checker;

    /**
     * @param \Permit\Permission\MergerInterface $merger
     **/
    public function __construct(MergerInterface $merger, CheckerInterface $checker)
    {
        $this->merger = $merger;
        $this->checker = $checker;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Permit\User\UserInterface $user The Holder of permission codes
     * @param $mixed $resource The resource
     * @param mixed $context (optional)
     * @return bool
     **/
    public function hasAccess(UserInterface $user, $resource, $context='default')
    {

        if (!$user instanceof Holder || !$resource instanceof Holder) {
            return;
        }

        $otherCodes = $this->collectOtherPermissionCodes($resource);

        return $this->checker->hasAccess($user, $otherCodes, $context);

    }

    protected function collectOtherPermissionCodes(Holder $holder)
    {
        $codes = [];

        $otherPermissions = $this->merger->getMergedPermissions($holder);

        foreach ($otherPermissions as $code=>$access) {
            if ($access) {
                $codes[] = $code;
            }
        }

        return $codes;

    }

}