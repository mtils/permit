<?php namespace Permit\Groups\Template;

use Permit\Groups\HolderInterface as GroupHolderInterface;

interface ProviderInterface{

    /**
     * @brief Returns the group template for to create of $holder. If no
     *        category is found an excepion should be thrown
     *
     * @param \Permit\Groups\Template\HolderInterface $holder
     * @param bool $forRegistration (default:true)
     * @return \Permit\Groups\Template\TemplateInterface
     **/
    public function getTemplateFor(GroupHolderInterface $holder, $forRegistration=false);

    /**
     * @brief Returns all templates the holder $holder is allowed to use
     *
     * @param \Permit\Groups\HolderInterface $holder
     * @return \Traversable List of templates
     **/
    public function getAllowedTemplatesFor(GroupHolderInterface $holder, $forRegistration=false);

    /**
     * @brief Returns all available templates
     *
     * @return Traversable of group templates
     **/
    public function all();

}