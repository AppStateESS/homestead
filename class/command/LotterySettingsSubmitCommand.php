<?php

class LotterySettingsSubmitCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'LotterySettingsSubmit');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'lottery_admin')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to administer re-application features.');
        }

        $viewCmd = CommandFactory::getCommand('ShowLotterySettings');

        $lotteryTerm = $context->get('lottery_term');

        $hardCap = $context->get('hard_cap');
        $yield = $context->get('expected_yield');
        
        $sophGoal   = !is_null($context->get('soph_goal'))?$context->get('soph_goal'):0;
        $jrGoal     = !is_null($context->get('jr_goal'))?$context->get('jr_goal'):0;
        $srGoal     = !is_null($context->get('sr_goal'))?$context->get('sr_goal'):0;

        PHPWS_Settings::set('hms', 'lottery_term', $lotteryTerm);
        PHPWS_Settings::set('hms', 'lottery_hard_cap', $hardCap);
        PHPWS_Settings::set('hms', 'lottery_expected_yield', $yield);
        
        PHPWS_Settings::set('hms', 'lottery_soph_goal', $sophGoal);
        PHPWS_Settings::set('hms', 'lottery_jr_goal', $jrGoal);
        PHPWS_Settings::set('hms', 'lottery_sr_goal', $srGoal);

        PHPWS_Settings::save('hms');

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Lottery settings saved.');
        $viewCmd->redirect();
    }
}

?>