<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class CreateWaiverCommand extends Command {

    public function getRequestVars()
    {
        return array('action' => 'CreateWaiver');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to administer re-application features.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Eligibility_Waiver.php');

        $usernames = split("\n", $context->get('usernames'));
        $term = PHPWS_Settings::get('hms', 'lottery_term');

        $error = false;
        foreach($usernames as $user){
            $waiver = new HMS_Eligibility_Waiver(trim($user),$term);
            $result = $waiver->save();
            if(!$result){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error creating wavier for: ' . $user );
                $error = true;
            }
        }

        if(!$error){
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Waivers created successfully.');
        }

        $cmd = CommandFactory::getCommand('ShowLotteryEligibilityWaiver');
        $cmd->redirect();
    }
}
?>
