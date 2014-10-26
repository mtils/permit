<?php namespace Permit\Groups\Template;

use Permit\Groups\HolderInterface;

/**
 * @brief A Template is a set of groups which will be automatically assigned to
 *        a user (on creation or registration)
 *        I use this functionality often in combination with a UserCategory.
 *        If you have a User->belongsTo(UserCategory) the category can determine
 *        which groups the user will be assigned to on activation.
 *        The UserCategory has an m:n groups pivot and an "showInRegistration"
 *        property which determines if the category is choosable in a registration
 *        form. Then on activation (DOI or manual) the groups of the template
 *        will be added to the newly activated user
 *
 **/

interface TemplateInterface{

    /**
     * @brief Returns all groups this template contains
     *
     * @return \Traversable of Permit\Groups\GroupInterface
     **/
    public function getDefaultGroups();

}