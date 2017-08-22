<?php

namespace Homestead;

class ReapplicationMaintenanceMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();

        if(UserStatus::isAdmin()){
            if(Current_User::allow('hms', 'lottery_admin')){
                $this->addCommandByName('Settings', 'ShowLotterySettings');
                $this->addCommandByName('Create entry', 'ShowLotteryAdminEntry');
                $this->addCommandByName('Set automatic winners', 'ShowLotteryAutoWinners');
                $this->addCommandByName('Eligibility waivers', 'ShowLotteryEligibilityWaiver');
            }

            if(Current_User::allow('hms', 'special_interest_approval')){
                $this->addCommandByName('Interest group approval', 'ShowSpecialInterestGroupApproval');
            }

            if(Current_User::allow('hms', 'lottery_admin')){
                $this->addCommandByName('Send Lottery Invites', 'ShowSendLotteryInvites');
                $this->addCommandByName('Re-Application waiting list', 'ShowLotteryWaitingList');
                $this->addCommandByName('Open Waiting list', 'ShowOpenWaitingList');
            }
        }
    }

    public function show()
    {
        if(empty($this->commands)){
            return "";
        }

        $tpl = array();

        $tpl['MENU'] = parent::show();

        return \PHPWS_Template::process($tpl, 'hms', 'admin/menus/ReapplicationMaintenanceMenu.tpl');
    }
}
