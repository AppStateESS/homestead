<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

class SubmitRoomChangeRequestCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRoomChangeRequest');
    }

    public function execute(CommandContext $context){
        // Cmd to redirect to when we're done or upon error.
        $cmd = CommandFactory::getCommand('StudentRoomChange');
        $successCmd = CommandFactory::getCommand('ShowStudentMenu');

        $cellNum = $context->get('cell_num');
        $optOut  = $context->get('cell_opt_out');

        $first  = $context->get('first_choice');
        $second = $context->get('second_choice');

        $swap = $context->get('swap_with');

        // Check that a cell phone number was provided, or that the opt-out box was checked.
        if((!isset($cellNum) || empty($cellNum)) && !isset($optOut)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell phone number or check the box indicating you do not wish to provide it.');
            $cmd->redirect();
        }

        // Check the format of the cell phone number
        if(isset($cellNum)){
            // Filter out non-numeric characters
            $cellNum = preg_replace("/[^0-9]/", '', $cellNum);

            // Double check the length for the db (limit of 11 chars)
            if(strlen($cellNum) > 11){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell phone number or check the box indicating you do not wish to provide it.');
                $cmd->redirect();
            }
        }

        $reason = $context->get('reason');

        // Make sure a 'reason' was provided.
        if(!isset($reason) || empty($reason)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a brief explaniation of why you are requesting a room change.');
            $cmd->redirect();
        }

        //create the request object
        $request = RoomChangeRequest::getNew();
        $request->username = UserStatus::getUsername();
        $request->cell_phone = $cellNum;
        $request->reason = $context->get('reason');
        $request->change(new PendingRoomChangeRequest);

        //preferences
        if(!empty($first))
            $request->addPreference($first);
        if(!empty($second))
            $request->addPreference($second);

        //swap - make sure the other person has an assignment
        if(!empty($swap) && !is_null(HMS_Assignment::getAssignment($swap, Term::getSelectedTerm()))){
            $request->switch_with = $swap;
            $request->is_swap     = true;
        }

        //test($request,1);

        $request->save();

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your room change request has been received and is pending approval. You will be contacted by your Residence Director (RD) in the next 24-48 hours regarding your request.');
        $successCmd->redirect();
    }
}
?>
