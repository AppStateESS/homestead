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
        PHPWS_Core::initModClass('hms', 'SOAP.php');

        $usernames = split("\n", $context->get('usernames'));
        $term = PHPWS_Settings::get('hms', 'lottery_term');
        $soap = SOAP::getInstance();

        $error = false;
        foreach($usernames as $user){
            // Remove everything after '@'.
            $splode = explode('@', $user);
            $user = trim($splode[0]); # Username is at [0]

            if(!$soap->isValidStudent($user, $term)){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Invalid username: $user"  );
                $error = true;
            }else{
                $waiver = new HMS_Eligibility_Waiver($user,$term);
                $result = $waiver->save();
                if(!$result){
                    NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error creating waiver for: ' . $user );
                    $error = true;
                }
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
