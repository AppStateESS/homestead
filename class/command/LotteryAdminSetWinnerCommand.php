<?php

class LotteryAdminSetWinnerCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'LotteryAdminSetWinner');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to administer re-application features.');
        }

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $username = $context->get('asu_username');
        $term = Term::getSelectedTerm();

        $viewCmd = CommandFactory::getCommand('ShowLotteryAutoWinners');

        $application = HousingApplication::getApplicationByUser($username, $term);

        if(is_null($application)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'The requested student has not completed a re-application.');
            $viewCmd->redirect();
        }

        $winner = $context->get('magic');
        if(is_null($winner)){
            $application->magic_winner = 0;
        }else{
            $application->magic_winner = 1;
        }

        try{
            $application->save();
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error saving the student\'s application.');
            $viewCmd->redirect();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The student\'s application was updated successfully.');
        $viewCmd->redirect();
    }
}