<?php
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initModClass('hms', 'EditRlcView.php');

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

    function getRequestVars(){
        $vars = array();

        $vars['action'] = 'ShowEditRlc';
        $vars['id']     = $this->id;

        return $vars;
    }

    function execute(CommandContext $context){

        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'learning_community_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit RLCs.');
        }
        
        $view = new EditRlcView();

        $context->setContent($view->show());
    }
}
?>
