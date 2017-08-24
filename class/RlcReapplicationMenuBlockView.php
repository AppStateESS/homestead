<?php

namespace Homestead;

class RlcReapplicationMenuBlockView extends View {

    private $term;
    private $startDate;
    private $endDate;
    private $application;
    private $rlcApp;

    public function __construct($term, $startDate, $endDate, LotteryApplication $application = NULL, HMS_RLC_Application $rlcApp = NULL, HMS_RLC_Assignment $assignment = NULL){
        $this->term         = $term;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
        $this->application  = $application;
        $this->rlcApp       = $rlcApp;
        $this->assignment   = $assignment;
    }

    public function show(){
        $tpl = array();

        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";

        if(isset($this->assignment) && $this->assignment->getStateName() == 'declined'){
            // Student declined the invite
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['DECLINED'] = ""; //dummy tag
        }else if(isset($this->application) && !is_null($this->application->id) && isset($this->assignment) && $this->assignment->getStateName() == 'confirmed') {
            // Student has applied, been accepted, been invited, and confirmed that invitation to a particular community. The student can no longer view/edit the application.
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
            $tpl['CONFIRMED_RLC_NAME'] = $this->assignment->getRlcName();

        }else if(isset($this->assignment) && $this->assignment->getStateName() == 'invited'){
            // Studnet has applied, been assigned, and been sent an invite email
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;

            $tpl['INVITED_COMMUNITY_NAME'] = $this->assignment->getRlcName();

            $acceptCmd = CommandFactory::getCommand('ShowAcceptRlcInvite');
            $acceptCmd->setTerm($this->term);
            $tpl['INVITED_CONFIRM_LINK'] = $acceptCmd->getLink('accept or decline your invitation');

        }else if(!is_null($this->rlcApp)){
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
            // Student has not re-applied yet
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

        return \PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/rlcReapplicationMenuBlock.tpl');
    }
}
