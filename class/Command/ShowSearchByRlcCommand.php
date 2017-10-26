<?php

namespace Homestead\Command;

use \Homestead\SearchByRlcView;
use \Homestead\UserStatus;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

class ShowSearchByRlcCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowSearchByRlc');

        return $vars;
    }

    public function execute(CommandContext $context){

        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'view_rlc_members')){
            throw new PermissionException('You do not have permission to view RLC members.');
        }

        if( !is_null($context->get('rlc')) && is_numeric($context->get('rlc')) ){
            $viewCmd = CommandFactory::getCommand('ShowViewByRlc');
            $viewCmd->setRlcId($context->get('rlc'));
            $viewCmd->redirect();
        }
        $view = new SearchByRlcView();

        $context->setContent($view->show());
    }
}
