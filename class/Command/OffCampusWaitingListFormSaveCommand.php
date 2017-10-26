<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\NotificationView;
use \Homestead\WaitingListApplication;
use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_Email;
use \Homestead\Term;

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
        $term = $context->get('term');

        $errorCmd = CommandFactory::getCommand('ShowOffCampusWaitListApplication');
        $errorCmd->setTerm($term);

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Data sanity checking
        $doNotCall   = $context->get('do_not_call');
        $phoneNumber = $context->get('number');

        if(is_null($doNotCall)){
            // do not call checkbox was not selected, so check the number
            if($phoneNumber === ''){
                \NQ::simple('hms', NotificationView::ERROR, 'Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
                $errorCmd->redirect();
            }

            $phoneNumber = trim($phoneNumber);

        } else {
            $phoneNumber = null;
        }

        $mealPlan = $context->get('meal_option');

        $waitlistReason = $context->get('waitlist_reason');
        $oncampusReason = $context->get('oncampus_reason');
        $oncampusOtherReason = $context->get('oncampus_other_reason');

        $international = $student->isInternational();

        $application = new WaitingListApplication(0, $term, $student->getBannerId(), $student->getUsername(), $student->getGender(), $student->getType(), $student->getApplicationTerm(), $phoneNumber, $mealPlan, $international, $waitlistReason, $oncampusReason, $oncampusOtherReason);

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
            \NQ::simple('hms', NotificationView::ERROR, 'There was an error saving your application. Please try again or contact the Department of University Housing.');
            $errorCmd->redirect();
        }

        // Log the fact that the entry was saved
        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_ENTRY, UserStatus::getUsername());

        // Send a confirmation email
        $year = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));
        HMS_Email::sendWaitListApplicationConfirmation($student, $year);

        // Show a sucess message and redirect to the main menu
        \NQ::simple('hms', NotificationView::SUCCESS, 'Your application to the Open Waiting List was submitted successfully.');
        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}
