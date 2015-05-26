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

        $depositAgreed = $context->get('deposit_check');

        if(is_null($depositAgreed)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'You must check the box indicating you understand the License Contract deposit fees.');
            $errorCmd->redirect();
        }

        // Data sanity checking
        $doNotCall  = $context->get('do_not_call');
        $number     = $context->get('number');

        if(is_null($doNotCall)){
            // do not call checkbox was not selected, so check the number

            if(is_null($number)){
                NQ::simple('hms', hms\NotificationView::ERROR, 'Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
                $errorCmd->redirect();
            }
        }
        
        //$mealPlan = $context->get('meal_plan');

        /* Emergency Contact Sanity Checking */
        $emergencyName = $context->get('emergency_contact_name');
        $emergencyRelationship = $context->get('emergency_contact_relationship');
        $emergencyPhone = $context->get('emergency_contact_phone');
        $emergencyEmail = $context->get('emergency_contact_email');

        if (empty($emergencyName) || empty($emergencyRelationship) || empty($emergencyPhone) || empty($emergencyEmail)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please complete all of the emergency contact person information.');
            $errorCmd->redirect();
        }


        /* Missing Persons Sanity Checking */
        $missingPersonName = $context->get('missing_person_name');
        $missingPersonRelationship = $context->get('missing_person_relationship');
        $missingPersonPhone = $context->get('missing_person_phone');
        $missingPersonEmail = $context->get('missing_person_email');

        if (empty($missingPersonName) || empty($missingPersonRelationship) || empty($missingPersonPhone) || empty($missingPersonEmail)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please complete all of the missing persons contact information.');
            $errorCmd->redirect();
        }

        // Sorority stuff
        if(!is_null($context->get('sorority_check'))){
            $sorority = $context->get('sorority_drop');
            if($sorority == 'none'){
                NQ::simple('hms', hms\NotificationView::ERROR, 'Please select your sorority from the drop down menu.');
                $errorCmd->redirect();
            }

            $sororityPref = $context->get('sorority_pref');
            if(is_null($sororityPref)){
                NQ::simple('hms', hms\NotificationView::ERROR, 'Please indicate your preference for APH or central-campus housing.');
                $errorCmd->redirect();
            }
        }

        // Teaching Fellows check
        /*
        if($student->isTeachingFellow() && is_null($context->get('tf_pref'))){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please indicate your preference for Teaching Fellow housing.');
            $errorCmd->redirect();
        }
        */

        // Watauga Global check
        if($student->isWataugaMember() && is_null($context->get('wg_pref'))){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please indicate your preference for Watauga Global housing.');
            $errorCmd->redirect();
        }

        // Honors check
        if($student->isHonors() & is_null($context->get('honors_pref'))){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Please indicate your preference for Honors housing.');
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
