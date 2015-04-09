<?php

class LotteryChooseRoomThanksView extends Homestead\View{
    
    private $room;
    
    public function __construct(HMS_Room $room)
    {
        $this->room = $room;
    }
    
    public function show()
    {
        $tpl = array();
        
        $tpl['LOCATION'] = $this->room->where_am_i();
        
        $mainMenuCmd = CommandFactory::getCommand('ShowStudentMenu');
        $tpl['MAIN_MENU'] = $mainMenuCmd->getLink('Return to the main menu');
        
        $tpl['LOGOUT'] = UserStatus::getLogoutLink();

        Layout::addPageTitle("Thank you");
        
        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_room_thanks.tpl');
    }
}

?>
