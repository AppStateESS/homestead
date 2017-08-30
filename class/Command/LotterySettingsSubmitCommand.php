<?php

namespace Homestead\Command;

use \Homestead\NotificationView;
use \Homestead\CommandFactory;
use \Homestead\Exception\PermissionException;

class LotterySettingsSubmitCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'LotterySettingsSubmit');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'lottery_admin')){
            throw new PermissionException('You do not have permission to administer re-application features.');
        }

        $viewCmd = CommandFactory::getCommand('ShowLotterySettings');

        $lotteryTerm = $context->get('lottery_term');

        $hardCap    = !is_null($context->get('hard_cap'))?$context->get('hard_cap'):0;
        $jrGoal     = !is_null($context->get('jr_goal'))?$context->get('jr_goal'):0;
        $srGoal     = !is_null($context->get('sr_goal'))?$context->get('sr_goal'):0;

        \PHPWS_Settings::set('hms', 'lottery_term', $lotteryTerm);
        \PHPWS_Settings::set('hms', 'lottery_hard_cap', $hardCap);

        \PHPWS_Settings::set('hms', 'lottery_jr_goal', $jrGoal);
        \PHPWS_Settings::set('hms', 'lottery_sr_goal', $srGoal);

        \PHPWS_Settings::save('hms');

        \NQ::simple('hms', NotificationView::SUCCESS, 'Lottery settings saved.');
        $viewCmd->redirect();
    }
}
