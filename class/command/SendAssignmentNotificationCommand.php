<?php

/**
 * Controller for sending Assignment Notifications
 *
 * @author jbooker
 * @package HMS
 */
class SendAssignmentNotificationCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'SendAssignmentNotification');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'assignment_notify')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to send assignment notifications.');
        }

        PHPWS_Core::initModClass('hms', 'Term.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');

        // Check if any move-in times are set for the selected term
        $moveinTimes = HMS_Movein_Time::get_movein_times_array(Term::getSelectedTerm());

        // If the array of move-in times ONLY has the zero-th element ['None'] then it's no good
        // Or, of course, if the array is null or emtpy it is no good
        if(count($moveinTimes) <= 1 || is_null($moveinTimes) || empty($moveinTimes)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There are no move-in times set for '.Term::getPrintableSelectedTerm());
            $context->goBack();
        }

        // Keep track of floors missing move-in times
        $missingMovein = array();

        $term = Term::getSelectedTerm();

        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('email_sent', 0);
        $db->addWhere('term', $term);

        $result = $db->getObjects("HMS_Assignment");

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        foreach($result as $assignment){
            //get the students real name from their asu_username
            $student = StudentFactory::getStudentByUsername($assignment->getUsername(), $term);

            //get the location of their assignment
            PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
            $bed = new HMS_Bed($assignment->getBedId());
            $room = $bed->get_parent();
            $location = $bed->where_am_i() . ' - Bedroom ' . $bed->bedroom_label;

            // Lookup the floor and hall to make sure the
            // assignment notifications flag is true for this hall
            $floor = $room->get_parent();
            $hall  = $floor->get_parent();

            if($hall->assignment_notifications == 0){
                continue;
            }

            // Get the student type for determining move-in time
            $type = $student->getType();

            // Check for an accepted and confirmed RLC assignment
            $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), $term);

            // If there is an assignment, make sure the student "confirmed" the rlc invite
            if(!is_null($rlcAssignment)){
                if($rlcAssignment->getStateName() != 'confirmed'){
                    $rlcAssignment = null;
                }
            }

            // Make sure this is re-initialized
            $moveinTimeId = null;
            $rlcSetMoveinTime = false;

            // Determine the move-in time
            if(!is_null($rlcAssignment)){
                // If there is a 'confirmed' RLC assignment, use the RLC's move-in times
                $rlc = $rlcAssignment->getRlc();

                if($type == TYPE_CONTINUING){
                    $moveinTimeId = $rlc->getContinuingMoveinTime();
                }else if($type == TYPE_TRANSFER){
                    $moveinTimeId = $rlc->getTransferMoveinTime();
                }else if($type == TYPE_FRESHMEN){
                    $moveinTimeId = $rlc->getFreshmenMoveinTime();
                }
            }
            
            // If there's a non-null move-in time ID at this point, then we know the RLC must have set it
            if(!is_null($moveinTimeId)){
                $rlcSetMoveinTime = true;
            }

            // If the RLC didn't set a movein time, set it according to the floor
            if(is_null($moveinTimeId)){
                if($type == TYPE_CONTINUING){
                    $moveinTimeId = $assignment->get_rt_movein_time_id();
                }else if($type == TYPE_TRANSFER){
                    $moveinTimeId = $assignment->get_t_movein_time_id();
                }else{
                    $moveinTimeId = $assignment->get_f_movein_time_id();
                }
            }

            // Check for missing move-in times
            if($moveinTimeId == NULL){
                //test($assignment, 1); // Will only happen if there's no move-in time set for the floor,student type
                // Lets only keep a set of the floors
                if(!in_array($floor, $missingMovein)){
                    $missingMovein[] = $floor;
                }

                // Missing move-in time, so skip to the next assignment
                continue;
            }

            // TODO: Grab all the move-in times and index them in an array by ID so we don't have to query the DB every single time
            $movein_time_obj = new HMS_Movein_Time($moveinTimeId);
            $movein_time = $movein_time_obj->get_formatted_begin_end();

            // Add a bit of text if the move-in time was for an RLC
            if($rlcSetMoveinTime){
                $movein_time .= ' (for the ' . $rlc->get_community_name() . ' Residential Learning Community)'; 
            }
            
            //get the list of roommates
            $roommates = array();

            $beds = $room->get_beds();
            foreach($beds as $bed){
                $roommate = $bed->get_assignee();
                if($roommate == false || is_null($roommate) || $roommate->getUsername() == $student->getUsername()){
                    continue;
                }

                $roommates[] = $roommate->getFullName() . ' ('. $roommate->getUsername() . '@appstate.edu) - Bedroom ' . $bed->bedroom_label;
            }

            // Send the email
            HMS_Email::sendAssignmentNotice($student->getUsername(), $student->getName(), $term, $location, $roommates, $movein_time);

            // Mark the student as having received an email
            $db->reset();
            $db->addWhere('asu_username', $assignment->getUsername());
            $db->addWhere('term', $term);
            $db->addValue('email_sent', 1);
            $rslt = $db->update();

            if(PHPWS_Error::logIfError($rslt)){
                throw new DatabaseException($result->toString());
            }
        }

        // Check for floors with missing move-in times.
        if(empty($missingMovein) || is_null($missingMovein)){
            // Ther are none, so show a success message
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "Assignment notifications sent.");
        }
        else {
            // Show a warning for each floor that was missing a move-in time
            foreach($missingMovein as $floor){
                $hall = $floor->get_parent();
                $text = $floor->getLink($hall->getHallName()." floor ") . " move-in times not set.";
                NQ::simple('hms', HMS_NOTIFICATION_WARNING, $text);
            }
        }

        $context->goBack();
    }
}

?>