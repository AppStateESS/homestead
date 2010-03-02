<?php

class OffCampusWaitingListFormSaveCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'OffCampusWaitingListFormSave', 'term'=>$this->term);

        if(isset($this->context)){
            return array_merge($vars, $this->context->getParams());
        }else{
            return $vars;
        }
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'WaitingListApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        
        $term = $context->get('term');
        
        # Double check that the student was not eligible to re-apply, and is therefore
        # eligible to apply to the wait list
        if(HMS_Lottery::determineEligibility(UserStatus::getUsername())){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You are not eligible to apply for the on-campus housing Open Waiting List for this semester.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }

        $errorCmd = CommandFactory::getCommand('ShowOffCampusWaitListApplication');
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
        
        
        $application = new WaitingListApplication(0, $term, $student->getBannerId(), $student->getUsername(), $student->getGender(), $student->getType(), $student->getApplicationTerm(), $cellPhone, $mealPlan, $physicalDisability, $psychDisability, $genderNeed, $medicalNeed);
        
        try{
            $application->save();
        }catch(Exception $e){
            test($e->getMessage(),1);
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error saving your application. Please try again or contact the Department of Housing & Residence Life.');
            $errorCmd->redirect();
        }

        # Log the fact that the entry was saved
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_ENTRY, UserStatus::getUsername());
        
        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));
        HMS_Email::sendWaitListApplicationConfirmation($student, $year);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your application to the Open Waiting List was submitted successfully.');
        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}

?>