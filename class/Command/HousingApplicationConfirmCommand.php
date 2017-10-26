<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\StudentFactory;
use \Homestead\ApplicationFeature;
use \Homestead\HousingApplicationFactory;
use \Homestead\HousingApplication;
use \Homestead\SpringApplication;
use \Homestead\SummerApplication;
use \Homestead\FallApplication;
use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_Email;
use \Homestead\MealPlan;
use \Homestead\UserStatus;
use \Homestead\Term;
use \Homestead\HMS_RLC_Application;
use \Homestead\NotificationView;
use \Homestead\ApplicationFeature\RlcApplicationRegistration;
use \Homestead\Exception\InvalidTermException;

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
        $term = $context->get('term');
        $username = UserStatus::getUsername();

        $student = StudentFactory::getStudentByUsername($username, $term);

        $sem = Term::getTermSem($term);

        // Check for an existing application and delete it
        $app_result = HousingApplication::checkForApplication($username, $term);

        // If there's an existing housing application, handle deleting it
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

            // Save the old created on dates for re-use on new application
            $oldCreatedOn = $application->getCreatedOn();
            $oldCreatedBy = $application->getCreatedBy();

            $application->delete();
        }

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
            default:
                throw new \Exception('Unknown application type');
        }

        $application = HousingApplicationFactory::getApplicationFromSession($_SESSION['application_data'], $term, $student, $appType);

        // If old created dates exist, use them as the 'created on' dates
        if(isset($oldCreatedOn))
        {
            $application->setCreatedOn($oldCreatedOn);
            $application->setCreatedBy($oldCreatedBy);
        }

        $application->setCancelled(0);

        // Hard code a summer meal option for all summer applications.
        // Application for other terms use whatever the student selected
        if($sem == TERM_SUMMER1 || $sem == TERM_SUMMER2){
            $application->setMealPlan(MealPlan::BANNER_MEAL_SUMMER);
        }

        $result = $application->save();

        if($result == TRUE){
            // Log the fact that the application was submitted
            HMS_Activity_Log::log_activity($username, ACTIVITY_SUBMITTED_APPLICATION, $username);

            try{
                // report the application to banner;
                $application->reportToBanner();
            }catch(\Exception $e){
                // ignore any errors reporting this to banner, they'll be logged and admins notified
                // we've saved the student's application locally, so it's ok if this doesn't work
            }

            // Send the email confirmation
            HMS_Email::send_hms_application_confirmation($student, $application->getTerm());

        }

        $friendly_term = Term::toString($application->getTerm());
        \NQ::simple('hms', NotificationView::SUCCESS, "Your application for $friendly_term was successfully processed!  You will receive an email confirmation in the next 24 hours.");

        $rlcReg = new RlcApplicationRegistration();


        if(ApplicationFeature::isEnabledForStudent($rlcReg, $term, $student)
                && HMS_RLC_Application::checkForApplication($student->getUsername(), $term) == FALSE
                && $application->rlc_interest == 1)
        {
            $rlcCmd = CommandFactory::getCommand('ShowRlcApplicationPage1View');
            $rlcCmd->setTerm($term);
            $rlcCmd->redirect();
        }else{
            $successCmd = CommandFactory::getCommand('ShowStudentMenu');
            $successCmd->redirect();
        }
    }
}
