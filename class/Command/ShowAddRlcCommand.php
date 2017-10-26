<?php

namespace Homestead\Command;

use \Homestead\HMS_Learning_Community;
use \Homestead\AddCommunityView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class ShowAddRlcCommand extends Command {
    protected $id;

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function getRequestVars(){
        $vars = array();

        $vars['action'] = 'ShowAddRlc';

        if(isset($this->id)){
            $vars['id'] = $this->id;
        }

        return $vars;
    }

    public function execute(CommandContext $context){
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'learning_community_maintenance')) {
            throw new PermissionException('You do not have permission to edit learning communities.');
        }

        $communityId = $context->get('id');

        if(isset($communityId)){
            $community = new HMS_Learning_Community($communityId);
        }else{
            $community = null;
        }

        $view = new AddCommunityView($community);

        $context->setContent($view->show());
    }
}
