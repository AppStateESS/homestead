<?php

class ReApplicationFormSubmitCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action'=>'ReApplicationFormSubmit', 'term'=>$this->term);
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        
        $term       = $context->get('term');
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $errorCmd = CommandFactory::getCommand('ShowReApplication');
        $errorCmd->setTerm($term);
        $errorCmd->setAgreedToTerms(1);

        $depositAgreed = $context->get('deposit_check');

        if(is_null($depositAgreed)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must check the box indicating you understand the License Contract deposit fees.');
            $errorCmd->redirect();
        }

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

        $mealOption         = $context->get('meal_option');

        # Sanity checks on preferred roommate user names
        $roommate1 = $context->get('roommate1');
        $roommate2 = $context->get('roommate2');
        $roommate3 = $context->get('roommate3');
        
        $roommates = array();

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

            $roommateStudent = StudentFactory::getStudentByUsername($roomie, $term);
            $bannerId = $roommateStudent->getBannerId();

            if(!isset($bannerId) || is_null($bannerId) || empty($bannerId)){
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
        }
        
        $specialNeed = $context->get('special_need');

        if(isset($specialNeed)){
            $onSubmitCmd = CommandFactory::getCommand('ReApplicationFormSave');
            $onSubmitCmd->loadContext($context);
            $onSubmitCmd->setTerm($term);

            $specialNeedCmd = CommandFactory::getCommand('ShowSpecialNeedsForm');
            $specialNeedCmd->setTerm($term);
            $specialNeedCmd->setVars($context->getParams());
            $specialNeedCmd->setOnSubmitCmd($onSubmitCmd);
            $specialNeedCmd->redirect();
        }else{
            //TODO
            $reviewCmd = CommandFactory::getCommand('ReApplicationFormSave');
            $reviewCmd->setTerm($term);
            $reviewCmd->loadContext($context);
            $reviewCmd->redirect();
        }
    }
}

?>