<?php

class EmergencyContactConfirmCommand extends Command {

    private $vars;

    public function setVars(Array $vars){
        $this->vars = $vars;
    }

    public function getRequestVars()
    {
        $reqVars = $this->vars;
        unset($reqVars['module']);

        $reqVars['action'] = 'EmergencyContactConfirm';

        return $reqVars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');

        PHPWS_Core::initModClass('hms', 'exception/InvalidTermException.php');

        $term = $context->get('term');
        $username = UserStatus::getUsername();

        $student = StudentFactory::getStudentByUsername($username, $term);

        $sem = Term::getTermSem($term);

        // Check for an existing application and load it
        $application = NULL;
        $app_result = HousingApplication::checkForApplication($username, $term);

        if($app_result !== FALSE){
            switch($sem){
                case TERM_SPRING:
                    $application = new SpringApplication($app_result['id']);
                    break;
                case TERM_SUMMER1:
                case TERM_SUMMER2:
                    $application = new SummerApplication($app_result['id']);
                    break;
                case TERM_FALL:
                    $application = new FallApplication($app_result['id']);
                    break;
                default:
                    throw new InvalidTermException('Invalid term specified.');
            }
        } else {
            // TODO What if there is no application found? Should I cry?
            // Execution shouldn't be able to make it this far if an application doesn't exist.
            throw new Exception('No application found.');
        }

        // Update the Emergency Contact and Missing Person information

        /* Student's Phone Number*/
        $application->setCellPhone($context->get('cell_phone'));

        // TODO Sanity check all this new contact information
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

        // Save the modified application
        $result = $application->save();

        if($result == TRUE){
            // Log the fact that the application updated
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($username, ACTIVITY_EMERGENCY_CONTACT_UPDATED, $username);

            try{
                // report the application to banner;
                $application->reportToBanner();
            }catch(Exception $e){
                // ignore any errors reporting this to banner, they'll be logged and admins notified.
                // we've saved the student's application locally, so it's ok if this doesn't work.
            }

            // Send the email confirmation
            PHPWS_Core::initModClass('hms', 'HMS_Email.php');
            HMS_Email::send_emergency_contact_updated_confirmation($student, $application->getTerm());
        }

        // Notify user of success
        //$friendly_term = Term::toString($application->getTerm());
        //NQ::simple('hms', hms\NotificationView::SUCCESS, "Your Emergency Contact & Missing Person information for $friendly_term was successfully modified! You will receive an email confirmation in the next 24 hours.");

        // Redirect to the student menu
        $successCmd = CommandFactory::getCommand('ShowStudentMenu');
        $successCmd->redirect();
    }
}
