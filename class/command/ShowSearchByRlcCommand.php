<?php

PHPWS_Core::initModClass('hms', 'SearchByRlcView.php');

class ShowSearchByRlcCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowSearchByRlc');

        return $vars;
    }

    public function execute(CommandContext $context){

        if(!Current_User::allow('hms', 'view_rlc_members')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
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
?>
