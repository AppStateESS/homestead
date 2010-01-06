<?php

class DeleteRoommateGroupCommand extends Command {

    private $id;

    public function setId($id){
        $this->id = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'DeleteRoommateGroup', 'id'=>$this->id);
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'roommate_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to create/edit roommate groups.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');

        $id = $context->get('id');

        if(is_null($id)){
            throw new InvalidArgumentException('Missing roommate group id.');
        }

        $viewCmd = CommandFactory::getCommand('EditRoommateGroupsView');

        try{
            $roommate = new HMS_Roommate($id);
            $roommate->delete();
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Error deleting roommate group: ' . $e->getMessage());
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Roommate group successfully deleted.');
        $viewCmd->redirect();
    }
}

?>