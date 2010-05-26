<?php

class RoommateSelectionMenuBlockView extends View {

    private $student;
    private $startDate;
    private $editDate;
    private $endDate;
    private $term;

    public function __construct(Student $student, $startDate, $editDate, $endDate, $term)
    {
        $this->student      = $student;
        $this->startDate    = $startDate;
        $this->editDate     = $editDate;
        $this->endDate      = $endDate;
        $this->term         = $term;
    }

    public function show()
    {
        $tpl = array();

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');

        $roommate = HMS_Roommate::get_confirmed_roommate(UserStatus::getUsername(), $this->term);

        $requests = HMS_Roommate::countPendingRequests(UserStatus::getUsername(), $this->term);
        
        // @ suppresses warnings
        $tpl['DATES'] = @HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);

        // Roommate has been selected and confirmed
        if(!is_null($roommate)){
            $name = $roommate->getFullName();
            $tpl['ROOMMATE_MSG']  = "<b>$name</b> has confirmed your roommate request. Roommate requests are subject to space availability.";
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Open"/>';
            if(time() < $this->editDate){
                $cmd = CommandFactory::GetCommand('ShowRoommateBreak');
                $rm = HMS_Roommate::getByUsernames(UserStatus::getUsername(), $roommate->getUsername(), $this->term);
                $cmd->setRoommateId($rm->id);
                $tpl['ROOMMATE_BREAK'] = $cmd->getLink('Break roommate pairing');
            }
        }
        // Roommate selected hasn't started yet
        else if(time() < $this->startDate){
            $tpl['ROOMMATE_MSG']  = '<b>It is too early to choose a roommate.</b> You can choose a roommate on ' . HMS_Util::getFriendlyDate($this->startDate) . '.';
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
        }
        // Roommate selection is over dawg
        else if(time() > $this->endDate){
            $tpl['ROOMMATE_MSG'] = '<b>It is too late to choose a roommate.</b> The deadline passed on ' . HMS_Util::getFriendlyDate($this->endDate) . '.';
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/tango/emblem-readonly.png" alt="Locked"/>';
        }
        // Student has some roommate requests! 
        else if($requests > 0){
            $tpl['ROOMMATE_REQUESTS'] = HMS_Roommate::display_requests(UserStatus::getUsername(), $this->term);
            if($requests == 1) {
                $tpl['ROOMMATE_REQUESTS_MSG'] = "<b style='color: #F00'>You have a roommate request.</b> Please click the name below to confirm or reject the request.";
            }else{
                $tpl['ROOMMATE_REQUESTS_MSG'] = "<b style='color: #F00'>You have roommate requests.</b> Please click a name below to confirm or reject a request.";
            }
        }else if(HMS_Roommate::has_roommate_request(UserStatus::getUsername(),$this->term)) {
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/check.png" alt="Open"/>';
            $tpl['ROOMMATE_MSG'] = "<b>You have selected a roommate</b> and are awaiting their approval.";
            $requestee = HMS_Roommate::get_unconfirmed_roommate(UserStatus::getUsername(), $this->term);

            if(time() < $this->editDate){
                $rm = HMS_Roommate::getByUsernames(UserStatus::getUsername(), $requestee, $this->term);
                $cmd = CommandFactory::getCommand('RoommateRequestCancel');
                $cmd->setRoommateId($rm->id);
                $tpl['ROOMMATE_BREAK'] = $cmd->getLink('Cancel Request');
            }
        }else{
            $tpl['ICON'] = '<img class="status-icon" src="images/mod/hms/icons/arrow.png" alt="Open"/>';            
            $tpl['ROOMMATE_MSG'] = 'If you know who you want your roommate to be, <b>you may select your roommate now</b>. You will need to know your roommate\'s ASU user name (their e-mail address). You have until ' . HMS_Util::getFriendlyDate($this->endDate) . ' to choose a roommate. Click the link below to select your roommate.';
            $cmd = CommandFactory::getCommand('ShowRequestRoommate');
            $cmd->setTerm($this->term);
            $tpl['ROOMMATE_LINK'] = $cmd->getLink('Select Your Roommate');
        }

        Layout::addPageTitle("Roommate Selection");

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/roommateMenuBlock.tpl');
    }
}

?>
