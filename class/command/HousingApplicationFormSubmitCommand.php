<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class HousingApplicationFormSubmitCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        return array('action'=>'HousingApplicationFormSubmit', 'term'=>$this->term);
    }

    public function execute(CommandContext $context)
    {
        $term		= $context->get('term');
        $student	= StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $errorCmd = CommandFactory::getCommand('ShowHousingApplicationForm');
        $errorCmd->setTerm($term);
        $errorCmd->setAgreedToTerms(1);

        // Data sanity checking
        $doNotCall  = $context->get('do_not_call');
        $areaCode 	= $context->get('area_code');
        $exchange 	= $context->get('exchange');
        $number		= $context->get('number');

        if(is_null($doNotCall)){
            // do not call checkbox was not selected, so check the number
            if(is_null($areaCode) || is_null($exchange) || is_null($number)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
                $errorCmd->redirect();
            }
        }

        // TODO: this, right
        $sem = substr($term, 4, 2);
        if($sem == 10 || $sem == 40) {

            $mealOption			= $context->get('meal_option');
            $lifestyleOption	= $context->get('lifestyle_option');
            $preferredBedtime	= $context->get('preferred_bedtime');
            $roomCondition		= $context->get('room_condition');

            if(!is_numeric($mealOption) || !is_numeric($lifestyleOption) || !is_numeric($preferredBedtime) || !is_numeric($roomCondition))
            {
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid values entered. Please try again.');
                $errorCmd->redirect();
            }
        } else if($sem == 20 || $sem == 30) {
            $roomType = $context->get('room_type');

            if(!is_numeric($roomType)) {
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid values entered.  Please try again.');
                $errorCmd->redirect();
            }
        }

        //TODO add side thingie

        $specialNeed = $context->get('special_need');

        if(!isset($specialNeed)) {
            unset($_REQUEST['special_needs']);
        }

        $reviewCmd = CommandFactory::getCommand('ShowFreshmenApplicationReview');
        $reviewCmd->setTerm($term);

        if(isset($specialNeed)){
            $specialNeedCmd = CommandFactory::getCommand('ShowSpecialNeedsForm');
            $specialNeedCmd->setTerm($term);
            $specialNeedCmd->setVars($_REQUEST);
            $specialNeedCmd->setOnSubmitCmd($reviewCmd);
            $specialNeedCmd->redirect();
        }else{
            $reviewCmd->redirect();
        }
    }
}

?>
