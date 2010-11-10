<?php

class LotteryOptOutCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action'=>'LotteryOptOut');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');

        $errorCmd = CommandFactory::getCommand('LotteryShowWaitingListOptOut');

        # Check the captcha
        PHPWS_Core::initCoreClass('Captcha.php');
        $captcha = Captcha::verify(TRUE); // returns the words entered if correct, FALSE otherwise
        if($captcha === FALSE || is_null($captcha)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, the words you eneted were incorrect. Please try again.');
            $errorCmd->redirect();
        }

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $term = PHPWS_Settings::get('hms', 'lottery_term');

        $application = HousingApplication::getApplicationByUser(UserStatus::getUsername(), $term);

        $application->waiting_list_hide = 1;

        try{
            $application->save();
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, there was an error updating your housing application. Please try again or contact University Housing.');
            $errorCmd->redirect();
        }

        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_LOTTERY_OPTOUT, UserStatus::getUsername(), 'Captcha: ' . $captcha);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'You have been removed from the on-campus housing reapplication waiting list.');
        $successCmd = CommandFactory::getCommand('ShowStudentMenu');
        $successCmd->redirect();
    }
}

?>