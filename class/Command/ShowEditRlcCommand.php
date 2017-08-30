<?php

namespace Homestead\Command;

use \Homestead\EditRlcView;
use \Homestead\UserStatus;
use \Homestead\Exception\PermissionException;

class ShowEditRlcCommand extends Command {
    private $id;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        if(is_numeric($id))
        $this->id = $id;
    }

    public function getRequestVars(){
        $vars = array();

        $vars['action'] = 'ShowEditRlc';
        $vars['id']     = $this->id;

        return $vars;
    }

    public function execute(CommandContext $context){

        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'learning_community_maintenance')) {
            throw new PermissionException('You do not have permission to edit RLCs.');
        }

        $view = new EditRlcView();

        $context->setContent($view->show());
    }
}
