<?php

PHPWS_Core::initModClass('hms', 'RlcFactory.php');

class RlcSelfSelectionMenuBlockView extends homestead\View {
    
    private $term;
    private $startDate;
    private $endDate;
    
    private $rlcAssignment;
    private $roomAssignment;
    private $roommateRequests;
    
    
    public function __construct($term, $startDate, $endDate, HMS_RLC_Assignment $rlcAssignment = null, HMS_Assignment $roomAssignment = null, $roommateRequests)
    {
    	$this->term = $term;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        
        $this->rlcAssignment = $rlcAssignment;
        $this->roomAssignment = $roomAssignment;
        $this->roommateRequests = $roommateRequests;
    }
    
    public function show()
    {
    	$tpl = array();
        
        $tpl['DATES'] = HMS_Util::getPrettyDateRange($this->startDate, $this->endDate);
        $tpl['STATUS'] = "";
        
        if(!is_null($this->rlcAssignment) && !is_null($this->roomAssignment)){
            // Student is already assigned
            $tpl['ICON'] = FEATURE_COMPLETED_ICON;
        	$tpl['ASSIGNMENT'] = $this->roomAssignment->where_am_i();
            $tpl['ASSIGNED_COMMUNITY_NAME'] = $this->rlcAssignment->getRlcName();
            
        } else if(time() < $this->startDate) {
            // To early
        	$tpl['ICON'] = FEATURE_NOTYET_ICON;
            $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
            
        } else if(time() > $this->endDate) {
            // Too late
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            // fade out header
            $tpl['STATUS'] = "locked";
            $tpl['END_DEADLINE'] = HMS_Util::getFriendlyDate($this->endDate);
            
        } else if (is_null($this->rlcAssignment)) {
            // Student has no RLC assignment, therefore is not eligible
        	$tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['NOT_ELIGIBLE']      = ""; //dummy tag, text is in template
            
        } else if ($this->rlcAssignment->getStateName() == 'selfselect-invite') {
        	// Student has a pending invite to self-select a room
            $tpl['ICON'] = FEATURE_OPEN_ICON;
            $rlcId = $this->rlcAssignment->getRlcId();
            $rlcList = RlcFactory::getRlcList($this->term);
            $tpl['INVITED_COMMUNITY_NAME'] = $rlcList[$rlcId];
            $cmd = CommandFactory::getCommand('RlcSelfAssignStart');
            $cmd->setTerm($this->term);
            $tpl['SELECT_LINK'] = $cmd->getLink('accept the invitation and select a room');
            
        } else {
        	// Deadlines are open, but student isn't eligible
            $tpl['ICON'] = FEATURE_LOCKED_ICON;
            $tpl['NOT_ELIGIBLE']      = ""; //dummy tag, text is in template
        }
        
        // Show roommate requests, if any
        if(time() > $this->startDate && $this->roommateRequests != false && !is_null($this->roommateRequests) && $this->roomAssignment != true && !PEAR::isError($this->roomAssignment)){
            $tpl['roommates'] = array();
            $tpl['ROOMMATE_REQUEST'] = ''; // dummy tag
            foreach($this->roommateRequests as $invite){
                $cmd = CommandFactory::getCommand('LotteryShowRoommateRequest');
                $cmd->setRequestId($invite['id']);
                
                $roommie = StudentFactory::getStudentByUsername($invite['requestor'], $this->term);
                $tpl['roommates'][]['ROOMMATE_LINK'] = $cmd->getLink($roommie->getName());
            }
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/rlcSelfSelection.tpl');
    }
}