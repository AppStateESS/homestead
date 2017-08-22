<?php

namespace Homestead;

class LotteryConfirmedRoommateThanksView extends View {

    private $invite;
    private $bed;

    public function __construct($invite, $bed){
        $this->invite = $invite;
        $this->bed = $bed;
    }

    public function show()
    {
        $tpl = array();

        $tpl['SUCCESS'] = 'Your roommate request was successfully confirmed. You have been assigned to ' . $this->bed->where_am_i() . ".";
        $tpl['LOGOUT_LINK'] = UserStatus::getLogoutLink();

        $mainMenuCmd = CommandFactory::getCommand('ShowStudentMenu');
        $tpl['MAIN_MENU'] = $mainMenuCmd->getLink('Return to the main menu');

        Layout::addPageTitle("Thank you");

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_confirm_roommate_thanks.tpl');
    }
}
