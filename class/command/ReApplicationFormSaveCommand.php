<?php

namespace Homestead\command;

use \Homestead\Command;

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
            \NQ::simple('hms', NotificationView::ERROR, 'You are not eligible to re-apply for on-campus housing for this semester.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }


        $errorCmd = CommandFactory::getCommand('ShowReApplication');
        $errorCmd->setTerm($term);

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Data sanity checking
        $doNotCall  = $context->get('do_not_call');
        $number     = $context->get('number');

        if(is_null($doNotCall)){
            // do not call checkbox was not selected, so check the number
            if(is_null($number)){
                \NQ::simple('hms', NotificationView::ERROR, 'Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
                $errorCmd->redirect();
            }
        }


        if(!is_null($doNotCall)){
            $cellPhone = null;
        }else{
            $cellPhone = $number;
        }

        $mealPlan = $context->get('meal_plan');

        /**
         * Special interest housing groups
         */
        // Sororities - If they checked the box, and their pref is APH,
        // then record her sorority choice
        $sororityCheck = $context->get('sorority_check');
        if(isset($sororityCheck) && $context->get('sorority_pref') == 'aph'){
            $sororityPref = $context->get('sorority_drop');
        }else{
            $sororityPref = null;
        }

        // Teaching Fellows, Watauga Global, and Honors
        //$tfPref = ($context->get('tf_pref') == 'with_tf')?1:0;
        $wgPref = ($context->get('wg_pref') == 'with_wg')?1:0;
        $honorsPref = ($context->get('honors_pref') == 'with_honors')?1:0;

        // Learning Community Interest
        $rlcInterest = $context->get('rlc_interest');
        $rlcInterest = isset($rlcInterest)?1:0;

        // Contract early release
        $earlyRelease = $context->get('early_release');
        if($earlyRelease == 'no'){
            $earlyRelease = NULL;
        }

        // International
        $international = $student->isInternational();

        $magicWinner = 0;

        // The student's type should always be 'C' (continuing),
        // even if thes student began in the Spring.
        $studentType = 'C';

        /**************************
         * Emergency Contact Info *
         */

        $application = new LotteryApplication(0, $term, $student->getBannerId(), $student->getUsername(), $student->getGender(), $studentType, $student->getApplicationTerm(), $cellPhone, $mealPlan, $international, NULL, $magicWinner, $sororityPref, $wgPref, $honorsPref, $rlcInterest, $earlyRelease);

        $application->setEmergencyContactName($context->get('emergency_contact_name'));
        $application->setEmergencyContactRelationship($context->get('emergency_contact_relationship'));
        $application->setEmergencyContactPhone($context->get('emergency_contact_phone'));
        $application->setEmergencyContactEmail($context->get('emergency_contact_email'));

        $application->setEmergencyMedicalCondition($context->get('emergency_medical_condition'));

        $application->setMissingPersonName($context->get('missing_person_name'));
        $application->setMissingPersonRelationship($context->get('missing_person_relationship'));
        $application->setMissingPersonPhone($context->get('missing_person_phone'));
        $application->setMissingPersonEmail($context->get('missing_person_email'));

        try{
            $application->save();
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, 'There was an error saving your re-application. Please try again or contact the Department of University Housing.');
            $errorCmd->redirect();
        }

        // Log the fact that the entry was saved
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_ENTRY, UserStatus::getUsername());

        // Send email confirmation
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));
        HMS_Email::send_lottery_application_confirmation($student, $year);

        // Show success message
        \NQ::simple('hms', NotificationView::SUCCESS, 'Your re-application was submitted successfully.');

        // Redirect to the RLC Reapplication form is the student is interested in RLCs, otherwise, show the student menu
        if($rlcInterest == 1){
            $cmd = CommandFactory::getCommand('ShowRlcReapplication');
            $cmd->setTerm($term);
        }else{
            $cmd = CommandFactory::getCommand('ShowStudentMenu');
        }
        $cmd->redirect();
    }
}
