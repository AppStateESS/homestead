<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditFloorViewCommand extends Command {

    private $floorId;

    public function setFloorId($id){
        $this->floorId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'EditFloorView');

        if(isset($this->floorId)){
            $vars['floor'] = $this->floorId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() ||  !Current_User::allow('hms', 'floor_view') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit floors.');
        }

        // Check for a hall ID
        $floorId = $context->get('floor');
        if(!isset($floorId)){
            throw new InvalidArgumentException('Missing floor ID.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'FloorView.php');

        $floor = new HMS_Floor($floorId);

        if($floor->term != Term::getSelectedTerm()){
            $floorCmd = CommandFactory::getCommand('SelectFloor');
            $floorCmd->setTitle('Edit a Floor');
            $floorCmd->setOnSelectCmd(CommandFactory::getCommand('EditFloorView'));
            $floorCmd->redirect();
        }

        $hall = $floor->get_parent();
        $floorView = new FloorView($hall, $floor);

        $context->setContent($floorView->show());
    }
}
