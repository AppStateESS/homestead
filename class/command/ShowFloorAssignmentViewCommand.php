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

        # Create the floor object
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        $floor = new HMS_Floor($context->get('floor'));

        # Check for term mis-match
        if($floor->term != Term::getSelectedTerm()){
            $floorAssignCmd = CommandFactory::getCommand('SelectFloor');
            $floorAssignCmd->setOnSelectCmd(CommandFactory::getCommand('ShowFloorAssignmentView'));
            $floorAssignCmd->redirect();
        }

        $view = new FloorAssignmentView($floor);

        $context->setContent($view->show());
    }
}
?>
