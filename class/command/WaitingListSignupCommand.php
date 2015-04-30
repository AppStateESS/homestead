<?php

/**
 * Controller class to handle signing the current user up to the on-campus waiting list.
 * NB: This is for student who re-applied and didn't win/choose a room through the lottery.
 * 
 * @author jbooker
 * @package Hms
 */
class WaitingListSignupCommand extends Command {
    
    /**
     * (non-PHPdoc)
     * @see Command::getRequestVars()
     */
    public function getRequestVars()
    {
        return array('action'=>'WaitingListSignup');
    }

    /**
     * (non-PHPdoc)
     * @see Command::execute()
     */
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        
        $term = $context->get('term');
        
        if (!isset($term)) {
            throw new InvalidArgumentException('Missing term.');
        }
        
        $user = UserStatus::getUsername();
        $student = StudentFactory::getStudentByUsername($user, $term);
        
        // Load the student's application. Should be a lottery application.
        $application = HousingApplicationFactory::getAppByStudent($student, $term);
        
        // If there isn't a valid application in the DB, then we have a problem.
        if (!isset($application) || !$application instanceof LotteryApplication) {
            throw new InvalidArgumentException('Null application object.');
        }

        // Check to make sure the date isn't already set
        $time = $application->getWaitingListDate();
        if (isset($time)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'You have already applied for the waiting list.');
            $cmd = CommandFactory::getCommand('ShowStudentMenu');
            $cmd->redirect();
        }
        
        // Set the date
        $application->setWaitingListDate(time());
        
        // Save the application again
        $application->save();

        // Log it to the activity log
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_REAPP_WAITINGLIST_APPLY, UserStatus::getUsername());
        
        // Success command
        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}
?>
