<?php

namespace Homestead\command;

use \Homestead\Command;

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');

class ShowEmergencyContactReviewCommand extends Command {

    private $term;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getRequestVars()
    {
        $vars = $_REQUEST; // Carry forward the existing context

        // Overwrite the old action
        unset($vars['module']);
        $vars['action'] = 'ShowEmergencyContactReview';
        $vars['term']	= $this->term;

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $errorCmd = CommandFactory::getCommand('ShowEmergencyContactForm');
        $errorCmd->setTerm($term);

        // Determine the application type, based on the term
        $sem = Term::getTermSem($term);

        switch ($sem){
            case TERM_FALL:
                $appType = 'fall';
                break;
            case TERM_SPRING:
                $appType = 'spring';
                break;
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $appType = 'summer';
                break;
        }

        try{
            $application = HousingApplicationFactory::getAppByStudent($student, $term, $appType);

            // Change the emergency contact and missing person info temporarily, WITHOUT saving
            /* Emergency Contact */
            $application->setEmergencyContactName($context->get('emergency_contact_name'));
            $application->setEmergencyContactRelationship($context->get('emergency_contact_relationship'));
            $application->setEmergencyContactPhone($context->get('emergency_contact_phone'));
            $application->setEmergencyContactEmail($context->get('emergency_contact_email'));

            /* Emergency Medical Condition */
            $application->setEmergencyMedicalCondition($context->get('emergency_medical_condition'));

            /* Missing Person */
            $application->setMissingPersonName($context->get('missing_person_name'));
            $application->setMissingPersonRelationship($context->get('missing_person_relationship'));
            $application->setMissingPersonPhone($context->get('missing_person_phone'));
            $application->setMissingPersonEmail($context->get('missing_person_email'));

        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, $e->getMessage());
            $errorCmd->redirect();
        }

        PHPWS_Core::initModClass('hms', 'EmergencyContactReview.php');
        $view = new EmergencyContactReview($student, $term, $application);
        $context->setContent($view->show());
    }
}
