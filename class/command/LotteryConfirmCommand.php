<?php

class LotteryConfirmCommand extends Command {

    private $roomId;
    private $roommates;
    private $mealPlan;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function setRoommates($roommates){
        $this->roommates = $roommates;
    }

    public function setMealPlan($plan){
        $this->mealPlan = $plan;
    }

    public function getRequestVars(){
        $vars = array('action'=>'LotteryConfirm');

        $vars['roomId'] = $this->roomId;
        $vars['mealPlan'] = $this->mealPlan;
        $vars['roommates'] = $this->roommates;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $roomId = $context->get('roomId');
        $roommates = $context->get('roommates');
        $mealPlan = $context->get('mealPlan');

        $term = PHPWS_Settings::get('hms', 'lottery_term');
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $errorCmd = CommandFactory::getCommand('LotteryShowConfirm');
        $errorCmd->setRoomId($roomId);
        $errorCmd->setRoommates($roommates);
        $errorCmd->setMealPlan($mealPlan);

        PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE); // returns the words entered if correct, FALSE otherwise
        if($captcha === FALSE) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, the words you eneted were incorrect. Please try again.');
            $errorCmd->redirect();
        }

        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $room = new HMS_Room($roomId);

        test($roommates,1);
        
        foreach($roommates as $bed_id => $username){
            # Double check the student is valid
            try{
                $roommate = StudentFactory::getStudentByUsername($username, $term);
            }catch(StudentNotFoundException $e){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "$username is not a valid student. Please choose a different roommate.");
                $errorCmd->redirect();
            }
            
            # Make sure the bed is still empty
            $bed = new HMS_Bed($bed_id);

            if($bed->has_vacancy() != TRUE){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'One or more of the beds in the room you selected is no longer available. Please try again.');
                $errorCmd->redirect();
            }

            # Make sure none of the needed beds are reserved
            if($bed->is_lottery_reserved()){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'One or more of the beds in the room you selected is no longer available. Please try again.');
                $errorCmd->redirect();
            }

            # Double check the genders are all the same as the person logged in
            if($student->getGender() != $roommate->getGender()){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "$username is a different gender. Please choose a roommate of the same gender.");
                $errorCmd->redirect();
            }

            # Double check the genders are the same as the room (as long as the room isn't COED)
            if($room->gender_type != COED && $roommate->getGender() != $room->gender_type){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "$username is a different gender. Please choose a roommate of the same gender.");
                $errorCmd->redirect();
            }

            # Double check the students' elligibilities
            if(HMS_Lottery::determine_eligibility($username) !== TRUE){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "$username is not eligibile for assignment.");
                $errorCmd->redirect();
            }
        }

        # If the room's gender is 'COED' and no one is assigned to it yet, switch it to the student's gender
        if($room->gender_type == COED && $room->get_number_of_assignees() == 0){
            $room->gender_type = $student->getGender();
            $room->save();
        }

        # Assign the student to the requested bed
        $bed_id = array_search(UserStatus::getUsername(), $roommates); // Find the bed id of the student who's logged in

        $result = HMS_Assignment::assign_student(UserStatus::getUsername(), PHPWS_Settings::get('hms', 'lottery_term'), NULL, $bed_id, $this->mealPlan, 'Confirmed lottery invite', TRUE);

        if($result != E_SUCCESS){
            return Lottery_UI::show_select_roommates('Sorry, there was an error creating your room assignment. Please try again or contact Housing & Residence Life');
        }

        # Log the assignment
        HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_LOTTERY_ROOM_CHOSEN, $_SESSION['asu_username'], 'Captcha: ' . $captcha);

        $requestor_name = HMS_SOAP::get_name($_SESSION['asu_username']);

        foreach($roommates as $bed_id => $username){
            // Skip the current user
            if($username == $_SESSION['asu_username']){
                continue;
            }

            # Reserve the bed for the roommate
            $expires_on = mktime() + ROOMMATE_INVITE_TTL;
            $bed = &new HMS_Bed($bed_id);
            if(!$bed->lottery_reserve($username, $_SESSION['asu_username'], $expires_on)){
                $tpl['ERROR_MSG'] = "You were assigned, but there was a problem reserving space for your roommates. Please contact Housing & Residence Life.";
                return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_room_thanks.tpl');
            }

            HMS_Activity_Log::log_activity($username, ACTIVITY_LOTTERY_REQUESTED_AS_ROOMMATE, $_SESSION['asu_username'], 'Expires: ' . HMS_Util::get_long_date_time($expires_on));

            # Invite the selected roommates
            $name = HMS_SOAP::get_name($username);
            $term = PHPWS_Settings::get('hms', 'lottery_term');
            $year = HMS_Term::term_to_text($term, TRUE) . ' - ' . HMS_Term::term_to_text(HMS_Term::get_next_term($term),TRUE);
            HMS_Email::send_lottery_roommate_invite($username, $name, $expires_on, $requestor_name, $hall_room, $year);
        }
    }
}

?>