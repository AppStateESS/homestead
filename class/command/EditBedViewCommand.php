<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditBedViewCommand extends Command {

    private $bedId;

    function setBedId($id){
        $this->bedId = $id;
    }

    function getRequestVars()
    {
        $vars = array('action'=>'EditBedView');
         
        if(isset($this->bedId)){
            $vars['bed'] = $this->bedId;
        }
         
        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() ||  !Current_User::allow('hms', 'bed_view') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view beds.');
        }

        // Check for a bed ID
        $bedId = $context->get('bed');
         
        if(!isset($bedId)){
            throw new InvalidArgumentException('Missing bed ID.');
        }
         
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'BedView.php');
         
        $bed = new HMS_Bed($bedId);

        if($bed->term != Term::getSelectedTerm()){
            $bedCmd = CommandFactory::getCommand('SelectBed');
            $bedCmd->setTitle('Edit a Bed');
            $bedCmd->setOnSelectCmd(CommandFactory::getCommand('EditBedView'));
            $bedCmd->redirect();
        }

        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $hall = $floor->get_parent();
         
        $bedView = new BedView($hall, $floor, $room, $bed);
         
        $context->setContent($bedView->show());
    }
}

?>