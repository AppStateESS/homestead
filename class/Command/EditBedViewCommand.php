<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\Bed;
use \Homestead\BedView;
use \Homestead\Term;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 * @package hms
 */

class EditBedViewCommand extends Command {

    private $bedId;

    public function setBedId($id){
        $this->bedId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'EditBedView');

        if(isset($this->bedId)){
            $vars['bed'] = $this->bedId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() ||  !\Current_User::allow('hms', 'bed_view') ){
            throw new PermissionException('You do not have permission to view beds.');
        }

        // Check for a bed ID
        $bedId = $context->get('bed');

        if(!isset($bedId)){
            throw new \InvalidArgumentException('Missing bed ID.');
        }

        $bed = new Bed($bedId);

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
