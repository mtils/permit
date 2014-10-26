<?php namespace Permit\Groups\Template;

use Permit\Access\AssignerInterface;
use Permit\User\UserInterface;
use Permit\Groups\GroupRepositoryInterface;

class GroupAssigner implements AssignerInterface{

    protected $templateProvider;

    protected $groupRepo;

    public function __construct(ProviderInterface $templateProvider,
                                GroupRepositoryInterface $groupRepo){

        $this->templateProvider = $templateProvider;
        $this->groupRepo = $groupRepo;

    }

    /**
     * @brief Assigns the roles/groups/permissions to user $user
     *        Typically this is done while activating/registering a user
     *
     * @param Permit\User\UserInterface $user
     * @param $forActivation (default: true)
     * @return bool
     **/
    public function assignAccessRights(UserInterface $user, $forActivation=true){

        $template = $this->templateProvider->getTemplateFor($user, $forActivation);

        $templateGroupIds = [];

        foreach($template->getDefaultGroups() as $group){

            $templateGroupIds[] = $group->getGroupId();

            if(!$user->isInGroup($group)){
                $user->attachGroup($group);
            }

        }

        $notAssignedGroupIds = [];

        // Remove all not assigned groups
        foreach($this->groupRepo->all() as $group){
            if(!in_array($group->getGroupId(), $templateGroupIds)){
                if($user->isInGroup($group)){
                    $user->detachGroup($group);
                }
            }
        }

        return TRUE;

    }

}