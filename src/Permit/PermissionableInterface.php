<?php namespace Permit;

interface PermissionableInterface{

    /**
     * @brief A generic context of accessing an object
     * @var int
     **/
    const ACCESS = 'access';

    /**
     * @brief A context of altering an object
     * @var int
     **/
    const ALTER = 'alter';

    /**
     * @brief A context of destroing (deleting) an object
     * @var int
     **/
    const DESTROY = 'destroy';

    /**
     * @brief A generic context of accessing or altering a related object
     * @var int
     **/
    const RELATED = 'accessRelated';

    /**
     * @brief Returns a required permission code to access this
     *        object inside a $context context
     *
     * @param int $context A context to allow different cases
     * @return string A permission code
     **/
    public function requiredPermit($context=self::ACCESS);

}