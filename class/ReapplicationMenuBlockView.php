<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class ReapplicationMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $assignment;
    private $application;
    private $roommateRequests;

    public function __construct($term, $startDate, $endDate, HMS_Assignment $assignment = NULL, LotteryApplication $application = NULL, $roommateRequests)
    {
        $this->term               = $term;
        $this->startDate          = $startDate;
        $this->endDate            = $endDate;
        $this->application        = $application;
        $this->assignment         = $assignment;
        $this->roommateRequests   = $roommateRequests;
    }

    public function show()
    {
        $tpl = array();

        if(!is_null($this->assignment)) {
            # Student has already been assigned.
            $tpl['ASSIGNED'] = $this->assignment->where_am_i();
        }else if(!is_null($this->application) && $this->application->isWinner()){
            # Student has won, let them choose their room
            $tpl['EXPIRE_DATE'] = HMS_Util::get_long_date_time($this->application->invite_expires_on);
            $tpl['SELECT_LINK'] = PHPWS_Text::secureLink('Click here to select your room', 'hms', array('type'=>'student', 'op'=>'lottery_select_residence_hall'));
        }else if(!is_null($this->application)){
            # Student has already re-applied
            $tpl['ALREADY_APPLIED'] = ""; // dummy tag, text is in template
        }else if(time() < $this->startDate){
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->beginDate);
        }else if(time() > $this->endDate){
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        }else{
            if(HMS_Lottery::determineEligibility(UserStatus::getUsername())){
                $reAppCommand = CommandFactory::getCommand('ShowReApplication');
                $reAppCommand->setTerm($this->term);

                $tpl['ELIGIBLE']        = ""; //dummy tag, text is in template
                $tpl['LOTTERY_TERM_1']  = Term::toString($this->term);
                $tpl['NEXT_TERM_1']     = Term::toString(Term::getNextTerm($this->term));
                $tpl['ENTRY_LINK']      = $reAppCommand->getLink('Click here to re-apply.');
            }else{
                $tpl['NOT_ELIGIBLE']      = ""; //dummy tag, text is in template
                $tpl['LOTTERY_TERM_2']    = Term::toString($this->term);
                $tpl['NEXT_TERM_2']       = Term::toString(Term::getNextTerm($this->term));
            }
        }

        //TODO roommate requests!!

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/reApplicationMenuBlock.tpl');
    }
}

?>
