<?php

class ReApplicationFormSaveCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'ReApplicationFormSave', 'term'=>$this->term);

        if(isset($this->context)){
            return array_merge($vars, $this->context->getParams());
        }else{
            return $vars;
        }
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        // TODO Use the HousingApplicationFactory class to get all this data

        $term = $context->get('term');

        # Double check that the student is eligible
        if(!HMS_Lottery::determineEligibility(UserStatus::getUsername())){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You are not eligible to re-apply for on-campus housing for this semester.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }


        $errorCmd = CommandFactory::getCommand('ShowReApplication');
        $errorCmd->setTerm($term);
        $errorCmd->setAgreedToTerms(true);

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Data sanity checking
        $doNotCall  = $context->get('do_not_call');
        $areaCode   = $context->get('area_code');
        $exchange   = $context->get('exchange');
        $number     = $context->get('number');

        if(is_null($doNotCall)){
            // do not call checkbox was not selected, so check the number
            if(is_null($areaCode) || is_null($exchange) || is_null($number)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
                $errorCmd->redirect();
            }
        }


        if(!is_null($doNotCall)){
            $cellPhone = null;
        }else{
            $cellPhone = $areaCode . $exchange . $number;
        }

        $mealPlan = $context->get('meal_plan');

        $specialNeeds = $context->get('special_needs');
        $physicalDisability = isset($specialNeeds['physical_disability'])?1: 0;
        $psychDisability    = isset($specialNeeds['psych_disability'])?1: 0;
        $genderNeed         = isset($specialNeeds['gender_need'])?1: 0;
        $medicalNeed        = isset($specialNeeds['medical_need'])?1: 0;

        $roommate1 = $context->get('roommate1');
        $roommate2 = $context->get('roommate2');
        $roommate3 = $context->get('roommate3');

        $roommates = array();
        $roommateObjects = array();

        if($roommate1 != "" && !in_array($roommate1, $roommates)){
            $roommates[] = $roommate1;
        }

        if($roommate2 != "" && !in_array($roommate2, $roommates)){
            $roommates[] = $roommate2;
        }

        if($roommate3 != "" && !in_array($roommate3, $roommates)){
            $roommates[] = $roommate3;
        }

        # Sanity checks on preferred roommate user names
        foreach($roommates as $key => $roomie){
            # Check for invalid chars
            if(!PHPWS_Text::isValidInput($roomie)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "'$roomie' is an invalid user name. Hint: Your roommate's user name is the first part of his/her email address.");
                $errorCmd->redirect();
            }

            try {
                $roommateStudent = StudentFactory::getStudentByUsername($roomie, $term);
                $bannerId = $roommateStudent->getBannerId();
            }catch(StudentNotFoundException $e){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "'$roomie' is not a valid ASU user name. Hint: Your roommate's user name is the first part of his/her email address.");
                $errorCmd->redirect();
            }

            # Check to make sure the roommate is eligible for reapplication
            if(!HMS_Lottery::determineEligibility($roomie)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "'$roomie' is not eligible for re-application. Please try again.");
                $errorCmd->redirect();
            }

            if($roomie == UserStatus::getUsername()){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "You cannot choose yourself as a roommate, please try again.");
                $errorCmd->redirect();
            }

            if($roommateStudent->getGender() != $student->getGender()){
                NQ::simpe('hms', HMS_NOTIFICATION_ERROR, "$roomie is not the same gender as you. Please try again.");
                $errorCmd->redirect();
            }

            $roommateObjects[] = $roommateStudent;
        }

        $specialInterest = $context->get('special_interest');

        if($specialInterest == 'none'){
            $specialInterest = null;
        }

        $magicWinner = 0;

        $application = new LotteryApplication(0, $term, $student->getBannerId(), $student->getUsername(), $student->getGender(), $student->getType(), $student->getApplicationTerm(), $cellPhone, $mealPlan, $physicalDisability, $psychDisability, $genderNeed, $medicalNeed, $roommateObjects, $specialInterest, $magicWinner);

        try{
            $application->save();
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error saving your re-application. Please try again or contact the Department of Housing & Residence Life.');
            $errorCmd->redirect();
        }

        # Log the fact that the entry was saved
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_ENTRY, UserStatus::getUsername());

        # Send emails to request roommates
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        $requestor_name = $student->getFullName();
        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));

        # Send them all invite emails if they're not already entered in the lottery
        foreach($roommateObjects as $roomie){
            if(HousingApplication::checkForApplication($roomie->getUsername(), $term) === FALSE){
                HMS_Email::send_signup_invite($roomie->getUsername(), $roomie->getName(), $requestor_name, $year);
                HMS_Activity_Log::log_activity($roomie->getUsername(), ACTIVITY_LOTTERY_SIGNUP_INVITE, UserStatus::getUsername()); // log that we sent this invite
            }
        }

        HMS_Email::send_lottery_application_confirmation($student, $year);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your re-application was submitted successfully.');
        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}