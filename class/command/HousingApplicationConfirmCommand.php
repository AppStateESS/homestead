<?php

class HousingApplicationConfirmCommand extends Command {

    private $vars;

    public function setVars(Array $vars){
        $this->vars = $vars;
    }

    public function getRequestVars()
    {
        $reqVars = $this->vars;
        unset($reqVars['module']);

        $reqVars['action'] = 'HousingApplicationConfirm';

        return $reqVars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');

        PHPWS_Core::initModClass('hms', 'exception/InvalidTermException.php');

        $term = $context->get('term');
        $username = UserStatus::getUsername();

        $student = StudentFactory::getStudentByUsername($username, $term);

        $sem = Term::getTermSem($term);

        # Check for an existing application and delete it
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
                    break;
            }

            $application->delete();
        }

        // Hard code a sumemr meal option for all summer applications.
        // Application for other terms use whatever the student selected
        if($sem == TERM_SUMMER1 || $sem == TERM_SUMMER2){
            $mealPlan = BANNER_MEAL_5WEEK;
        }else{
            $mealPlan = $context->get('meal_option');
        }

        $specialNeeds = $context->get('special_needs');

        # Create a new application from the request data and save it
        if($sem == TERM_SUMMER1 || $sem == TERM_SUMMER2){
            $application = new SummerApplication(0, $term, $student->getBannerId(), $username,
            $student->getGender(),
            $student->getType(),
            $student->getApplicationTerm(),
            $context->get('area_code') . $context->get('exchange') . $context->get('number'),
            $mealPlan,
            isset($specialNeeds['physical_disability']) ? 1 : 0,
            isset($specialNeeds['psych_disability']) ? 1 : 0,
            isset($specialNeeds['gender_need']) ? 1 : 0,
            isset($specialNeeds['medical_need']) ? 1 : 0,
            ROOM_TYPE_DOUBLE
            );

        }else if ($sem == TERM_SPRING){
            $application = new SpringApplication(0, $term, $student->getBannerId(), $username,
            $student->getGender(),
            $student->getType(),
            $student->getApplicationTerm(),
            $context->get('area_code') . $context->get('exchange') . $context->get('number'),
            $mealPlan,
            isset($specialNeeds['physical_disability']) ? 1 : 0,
            isset($specialNeeds['psych_disability']) ? 1 : 0,
            isset($specialNeeds['gender_need']) ? 1 : 0,
            isset($specialNeeds['medical_need']) ? 1 : 0,
            $context->get('lifestyle_option'),
            $context->get('preferred_bedtime'),
            $context->get('room_condition'));
        }else if ($sem == TERM_FALL){
            $application = new FallApplication(0, $term, $student->getBannerId(), $username,
            $student->getGender(),
            $student->getType(),
            $student->getApplicationTerm(),
            $context->get('area_code') . $context->get('exchange') . $context->get('number'),
            $mealPlan,
            isset($specialNeeds['physical_disability']) ? 1 : 0,
            isset($specialNeeds['psych_disability']) ? 1 : 0,
            isset($specialNeeds['gender_need']) ? 1 : 0,
            isset($specialNeeds['medical_need']) ? 1 : 0,
            $context->get('lifestyle_option'),
            $context->get('preferred_bedtime'),
            $context->get('room_condition'),
            $context->get('rlc_interest'));
        }else{
            // Error because of invalid semester
            throw new InvalidTermException('Invalid term specified.');
        }

        $result = $application->save();

        $tpl = array();

        if($result == TRUE){
            # Log the fact that the application was submitted
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($username, ACTIVITY_SUBMITTED_APPLICATION, $username);

            try{
                # report the application to banner;
                $application->reportToBanner();
            }catch(Exception $e){
                // ignore any errors reporting this to banner, they'll be logged and admins notified
                // we've saved the student's application locally, so it's ok if this doesn't work
            }

            # Send the email confirmation
            PHPWS_Core::initModClass('hms', 'HMS_Email.php');
            HMS_Email::send_hms_application_confirmation($student, null);

        }

        $friendly_term = Term::toString($application->getTerm());
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "Your application for $friendly_term was successfully processed!  You will receive an email confirmation in the next 24 hours.");

        $successCmd = CommandFactory::getCommand('ShowStudentMenu');
        $successCmd->redirect();
    }
}

?>
