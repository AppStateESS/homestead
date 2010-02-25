<?php

class RoommateSelectionMenuBlockView extends View {
    
    private $student;
    private $startDate;
    private $endDate;
    private $term;
    
    public function __construct(Student $student, $startDate, $endDate, $term)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->term = $term;
    }
    
    public function show()
    {
        $tpl = array();

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $tpl['INTRO'] = 'Once you\'ve had a chance to communicate with your desired roommate and you have both agreed that you would like to room together, either of you can use the menu below to initiate an electronic handshake to confirm your desire to be roommates.';

        $roommate = HMS_Roommate::get_confirmed_roommate(UserStatus::getUsername(), $this->term);
            
        if(!is_null($roommate)){
            $name = $roommate->getFullName();
            $tpl['ROOMMATE_MSG']  = "<b>$name</b> has confirmed your roommate request. Roommate requests are subject to space availability.";
            $cmd = CommandFactory::GetCommand('ShowRoommateBreak');
            $rm = HMS_Roommate::getByUsernames(UserStatus::getUsername(), $roommate->getUsername(), $this->term);
            $cmd->setRoommateId($rm->id);
            $tpl['ROOMMATE_BREAK'] = $cmd->getLink('Break');
        }else{
            $requests = HMS_Roommate::countPendingRequests(UserStatus::getUsername(), $this->term);
            if($requests > 0) {
                $tpl['ROOMMATE_REQUESTS'] = HMS_Roommate::display_requests(UserStatus::getUsername(), $this->term);
                if($requests == 1) {
                    $tpl['ROOMMATE_REQUESTS_MSG'] = "<b style='color: #F00'>You have a roommate request.</b> Please click the name below to confirm or reject the request.";
                } else {
                    $tpl['ROOMMATE_REQUESTS_MSG'] = "<b style='color: #F00'>You have roommate requests.</b> Please click a name below to confirm or reject a request.";
                }
            } else {
                if(HMS_Roommate::has_roommate_request(UserStatus::getUsername(),$this->term)) {
                    $tpl['ROOMMATE_MSG'] = "<b>You have selected a roommate</b> and are awaiting their approval.";
                } else {
                    if(time() < $this->startDate) {
                        $tpl['ROOMMATE_MSG']  = '<b>It is too early to choose a roommate.</b> You can choose a roommate on ' . HMS_Util::getFriendlyDate($this->startDate) . '.';
                    } else if(time() > $this->endDate) {
                        $tpl['ROOMMATE_MSG'] = '<b>It is too late to choose a roommate.</b> The deadline passed on ' . HMS_Util::getFriendlyDate($this->endDate) . '.';
                    }else{
                        $tpl['ROOMMATE_MSG'] = 'If you know who you want your roommate to be, <b>you may select your roommate now</b>. You will need to know your roommate\'s ASU user name (their e-mail address). You have until ' . HMS_Util::getFriendlyDate($this->endDate) . ' to choose a roommate. Click the link below to select your roommate.';
                        $cmd = CommandFactory::getCommand('ShowRequestRoommate');
                        $cmd->setTerm($this->term);
                        $tpl['ROOMMATE_LINK'] = $cmd->getLink('Select Your Roommate');
                    }
                }
            }
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/roommateMenuBlock.tpl');
    }
}

?>
