<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class RlcReapplicationMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $application;
    private $rlcApp;

    public function __construct($term, $startDate, $endDate, LotteryApplication $application = NULL, HMS_RLC_Application $rlcApp = NULL){
        $this->term         = $term;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->application  = $application;
        $this->rlcApp       = $rlcApp;
    }

    public function show(){
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";

        if(!is_null($this->rlcApp)){
            # Student has already re-applied
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
            # Student has not re-applied yet
            if(is_null($this->application)){
                # No housing application, therefore not eligible
                $tpl['ICON'] = FEATURE_LOCKED_ICON;
                $tpl['NOT_ELIGIBLE']      = ""; //dummy tag, text is in template
                $tpl['LOTTERY_TERM_2']    = Term::toString($this->term);
                $tpl['NEXT_TERM_2']       = Term::toString(Term::getNextTerm($this->term));
            }else{
                # Eligible
                $tpl['ICON'] = FEATURE_OPEN_ICON;
                $reAppCommand = CommandFactory::getCommand('ShowRlcReapplication');
                $reAppCommand->setTerm($this->term);

                $tpl['ELIGIBLE']        = ""; //dummy tag, text is in template
                $tpl['LOTTERY_TERM_1']  = Term::toString($this->term);
                $tpl['NEXT_TERM_1']     = Term::toString(Term::getNextTerm($this->term));
                $tpl['ENTRY_LINK']      = $reAppCommand->getLink('Click here to re-apply.');
            }
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/rlcReapplicationMenuBlock.tpl');
    }
}

?>