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

        $type = $context->get('phase_radio');

        $per_soph   = !is_null($context->get('lottery_per_soph'))?$context->get('lottery_per_soph'):0;
        $per_jr     = !is_null($context->get('lottery_per_jr'))?$context->get('lottery_per_jr'):0;
        $per_senior = !is_null($context->get('lottery_per_senior'))?$context->get('lottery_per_senior'):0;

        $max_soph   = !is_null($context->get('lottery_max_soph'))?$context->get('lottery_max_soph'):0;
        $max_jr     = !is_null($context->get('lottery_max_jr'))?$context->get('lottery_max_jr'):0;
        $max_senior = !is_null($context->get('lottery_max_senior'))?$context->get('lottery_max_senior'):0;

        # if using single phase lottery, Make sure the percents add up to exactly 100
        if($type == 'single_phase' && ($per_soph + $per_jr + $per_senior) != 100){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: Percents must add up to 100');
            $viewCmd->redirect();
        }

        PHPWS_Settings::set('hms', 'lottery_term',       $lotteryTerm);

        PHPWS_Settings::set('hms', 'lottery_type',       $type);

        PHPWS_Settings::set('hms', 'lottery_per_soph',   $per_soph);
        PHPWS_Settings::set('hms', 'lottery_per_jr',     $per_jr);
        PHPWS_Settings::set('hms', 'lottery_per_senior', $per_senior);

        PHPWS_Settings::set('hms', 'lottery_max_soph',   $max_soph);
        PHPWS_Settings::set('hms', 'lottery_max_jr',     $max_jr);
        PHPWS_Settings::set('hms', 'lottery_max_senior', $max_senior);

        PHPWS_Settings::save('hms');

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Lottery settings saved.');
        $viewCmd->redirect();
    }
}

?>