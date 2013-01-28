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

        $term = $context->get('term');

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

        //$mealPlan = $context->get('meal_plan');

        // Sorority stuff
        if(!is_null($context->get('sorority_check'))){
            $sorority = $context->get('sorority_drop');
            if($sorority == 'none'){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please select your sorority from the drop down menu.');
                $errorCmd->redirect();
            }

            $sororityPref = $context->get('sorority_pref');
            if(is_null($sororityPref)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please indicate your preference for APH or central-campus housing.');
                $errorCmd->redirect();
            }
        }

        // Teaching Fellows check
        if($student->isTeachingFellow() && is_null($context->get('tf_pref'))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please indicate your preference for Teaching Fellow housing.');
            $errorCmd->redirect();
        }

        // Watauga Global check
        if($student->isWataugaMember() && is_null($context->get('wg_pref'))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please indicate your preference for Watauga Global housing.');
            $errorCmd->redirect();
        }

        // Honors check
        if($student->isHonors() & is_null($context->get('honors_pref'))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please indicate your preference for Honors housing.');
            $errorCmd->redirect();
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
            $reviewCmd = CommandFactory::getCommand('ReApplicationFormSave');
            $reviewCmd->setTerm($term);
            $reviewCmd->loadContext($context);
            $reviewCmd->redirect();
        }
    }
}

?>
