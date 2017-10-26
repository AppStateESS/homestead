<?php

namespace Homestead;

class ReapplicationMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $assignment;
    private $application;
    private $roommateRequests;

    public function __construct($term, $startDate, $endDate, HMS_Assignment $assignment = NULL, LotteryApplication $application = NULL, $roommateRequests)
    {
        $this->term             = $term;
        $this->startDate        = $startDate;
        $this->endDate          = $endDate;
        $this->application      = $application;
        $this->assignment       = $assignment;
        $this->roommateRequests = $roommateRequests;
    }

    public function show()
    {
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";

        $hardCapReached = LotteryProcess::hardCapReached($this->term);

        if(!is_null($this->assignment)) {
            // Student has already been assigned.
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            //$tpl['ASSIGNED'] = $this->assignment->where_am_i();
            $tpl['ASSIGNED'] = '';
        }else if($hardCapReached){
            // Hard cap has been reached
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['HARD_CAP'] = ""; // dummy tag
        }else if(!is_null($this->application) && $this->application->isWinner()){
            // Student has won, let them choose their room
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $chooseRoomCmd = CommandFactory::getCommand('LotteryShowChooseHall');
            $tpl['SELECT_LINK'] = $chooseRoomCmd->getLink('Click here to select your room');
        }else if(!is_null($this->application)){
            // Student has already re-applied
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $tpl['ALREADY_APPLIED'] = ""; // dummy tag, text is in template
        }else if(time() < $this->startDate){
            // Too early
            $tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else if(time() > $this->endDate){
            // Too late
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
        }else{
            if(HMS_Lottery::determineEligibility(UserStatus::getUsername())){
                $tpl['ICON'] = FEATURE_OPEN_ICON;
                $reAppCommand = CommandFactory::getCommand('ShowReApplication');
                $reAppCommand->setTerm($this->term);

                $tpl['ELIGIBLE']        = ""; //dummy tag, text is in template
                $tpl['LOTTERY_TERM_1']  = Term::toString($this->term);
                $tpl['NEXT_TERM_1']     = Term::toString(Term::getNextTerm($this->term));
                $tpl['ENTRY_LINK']      = $reAppCommand->getLink('Click here to re-apply.');
            }else{
                $tpl['ICON'] = FEATURE_LOCKED_ICON;
                $tpl['NOT_ELIGIBLE']      = ""; //dummy tag, text is in template
                $tpl['LOTTERY_TERM_2']    = Term::toString($this->term);
                $tpl['NEXT_TERM_2']       = Term::toString(Term::getNextTerm($this->term));
            }
        }

        if(!$hardCapReached && time() > $this->startDate){
            if($this->roommateRequests != FALSE && !is_null($this->roommateRequests) && $this->assignment != TRUE && !\PEAR::isError($this->assignment)){
                $tpl['roommates'] = array();
                $tpl['ROOMMATE_REQUEST'] = ''; // dummy tag
                foreach($this->roommateRequests as $invite){
                    $cmd = CommandFactory::getCommand('LotteryShowRoommateRequest');
                    $cmd->setRequestId($invite['id']);
                    $roommie = StudentFactory::getStudentByUsername($invite['requestor'], $this->term);
                    $tpl['roommates'][]['ROOMMATE_LINK'] = $cmd->getLink($roommie->getName());
                    //$tpl['roommates'][]['ROOMMATE_LINK'] = \PHPWS_Text::secureLink(HMS_SOAP::get_name($invite['requestor']), 'hms', array('type'=>'student', 'op'=>'lottery_show_roommate_request', 'id'=>$invite['id']));
                }
            }
        }

        return \PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/reApplicationMenuBlock.tpl');
    }
}
