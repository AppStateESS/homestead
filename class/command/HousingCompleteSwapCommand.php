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
        $cmd = CommandFactory::getCommand('HousingRoomChange');
        $cmd->tab = 'complete';

        if(!is_null($context->get('username'))){
            $rc0    = new RoomChangeRequest;
            $rc0    = $rc0->search($context->get('username'));
            $rc1    = $rc0->search($rc0->switch_with);

            /* Sanity check, if this happend something failed horribly. */
            if(!($rc0->state instanceof HousingApprovedChangeRequest)
               || !($rc1->state instanceof HousingApprovedChangeRequest)){
                throw new Exception("Both Room Change Requests must be approved before you can complete a swap");
            }
        } else {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Cannot complete room change for non-existant user!');
            $cmd->redirect();
        }

        //get both assignments
        $assignment0 = HMS_Assignment::getAssignment($rc0->username, Term::getSelectedTerm());
        $student0    = StudentFactory::getStudentByUsername($rc0->username, Term::getSelectedTerm());

        $assignment1 = HMS_Assignment::getAssignment($rc1->username, Term::getSelectedTerm());
        $student1    = StudentFactory::getStudentByUsername($rc1->username, Term::getSelectedTerm());

        //unassign the students
        HMS_Assignment::unassignStudent($student0, Term::getSelectedTerm(), "Room Change Swap - Unassign first", UNASSIGN_SWAP);
        HMS_Assignment::unassignStudent($student1, Term::getSelectedTerm(), "Room Change Swap - Unassign first", UNASSIGN_SWAP);

        //put the second student in the first student's former bed
        HMS_Assignment::assignStudent($student1, Term::getSelectedTerm(), NULL, $assignment0->bed_id, $assignment1->meal_option, "Room Change Swap - Reassign second to first", false, $assignment1->reason);

        //put the first student in the second's former bed
        HMS_Assignment::assignStudent($student0, Term::getSelectedTerm(), NULL, $assignment1->bed_id, $assignment0->meal_option, "Room Change Swap - Reassign first to second", false, $assignment0->reason);

        //update the state of the two requests
        if($rc0->change(new CompletedChangeRequest) && $rc0->save()
           && $rc1->change(new CompletedChangeRequest) && $rc1->save())
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Room Swap Completed');

        //and redirect
        $cmd->redirect();
    }
}

//?>