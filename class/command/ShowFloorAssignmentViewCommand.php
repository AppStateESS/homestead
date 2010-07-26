<?php

PHPWS_Core::initModClass('hms', 'FloorAssignmentView.php');
class ShowFloorAssignmentViewCommand extends Command {
    protected $floorId;

    public function setFloorId($id){
        $this->floorId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'ShowFloorAssignmentView');
    }

    public function execute(CommandContext $context){
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assign_by_floor')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to assign by floor!');
        }
        $view = new FloorAssignmentView($context->get('floor'));

        $context->setContent($view->show());
    }
}
?>
