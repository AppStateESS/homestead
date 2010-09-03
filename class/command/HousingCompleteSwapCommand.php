<?php
/*
 * HousingCompleteSwapCommand
 *
 *   Performs the swap of two students assignments for the Room Change
 * feature.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 */

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

class HousingCompleteSwapCommand extends Command {
    public $username;

    public function getRequestVars(){
        $vars = array('action'=>'HousingCompleteSwap');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    public function execute(CommandContext $context){
        if(!is_null($context->get('username'))){
            $rc0    = new RoomChangeRequest;
            $rc0    = $rc0->search($context->get('username'));
            $rc1    = $rc0->search($rc0->switch_with);
        } else {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Cannot complete room change for non-existant user!');
        }

        $assignment0 = HMS_Assignment::getAssignment($rc0->username, Term::getSelectedTerm());
        $assignment1 = HMS_Assignment::getAssignment($rc1->username, Term::getSelectedTerm());

        test(array($assignment0, $assignment1),1);
    }
}

//?>