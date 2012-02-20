<?php

/**
 * ReapplicationOverview report.
 * Gives various statistics related to re-application.
 * 
 * @author jbooker
 * @package HMS
 */

class ReapplicationOverview extends Report implements iCsvReport {
    
    const friendlyName = 'Reapplication Overview';
    const shortName = 'ReapplicationOverview';
    
    private $term;
    
    private $data;
    
    public function __construct($id = 0){
        parent::__construct($id);
        
        $this->data = array();
    }
    
    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        PHPWS_Core::initModClass('hms', 'LotteryProcess.php');
        
        $lotteryTerm = PHPWS_Settings::get('hms', 'lottery_term');
        
        // Number of entries total
        $this->data['LOTTERY_APPLICATIONS']    = PHPWS_DB::getOne("SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id WHERE (hms_new_application.term = $lotteryTerm)");
       
        // Number of entries by class/gender
        $this->data['SR_MALE_APPS'] = LotteryProcess::

        // Number of entries by gender
        $this->data['LOTTERY_FEMALE_APPLICATIONS'] = PHPWS_DB::getOne("SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id WHERE hms_new_application.term = $lotteryTerm AND gender = 1");
        $this->data['LOTTERY_MALE_APPLICATIONS'] = PHPWS_DB::getOne("SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id WHERE hms_new_application.term = $lotteryTerm AND gender = 0");
        
        // Number of entires by class
        $this->data['SOPH_APPLICATIONS']       = HMS_Lottery::count_applications_by_class($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['JR_APPLICATIONS']         = HMS_Lottery::count_applications_by_class($lotteryTerm, CLASS_JUNIOR);
        $this->data['SR_APPLICATIONS']         = HMS_Lottery::count_applications_by_class($lotteryTerm, CLASS_SENIOR);
        
        //TODO make this based on lottery assignment reason
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('term', $lotteryTerm);
        $db->addWhere('reason', 'lottery');
        $numLotteryAssigned = $db->select('count');
        
        // Assignments
        $this->data['LOTTERY_ASSIGNED']        = $numLotteryAssigned;
        
        // Assignments by class
        $this->data['SOPH_ASSIGNED']           = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['JR_ASSIGNED']             = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_JUNIOR);
        $this->data['SR_ASSIGNED']             = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SENIOR);

        // Invites sent
        $this->data['SOPH_INVITES']            = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['JR_INVITES']              = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_JUNIOR);
        $this->data['SR_INVITES']              = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SENIOR);

        $this->data['ROOMMATE_INVITES']        = LotteryProcess::countOutstandingRoommateInvites($lotteryTerm);
        
        // Remaining applications
        $this->data['SOPH_MALE_ENTRIES_REMAIN']   = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SOPHOMORE, FEMALE);
        $this->data['SOPH_FEMALE_ENTRIES_REMAIN'] = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SOPHOMORE, MALE);

        $this->data['JR_MALE_ENTRIES_REMAIN']   = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_JUNIOR, FEMALE);
        $this->data['JR_FEMALE_ENTRIES_REMAIN'] = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_JUNIOR, MALE);

        $this->data['SR_MALE_ENTRIES_REMAIN']   = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SENIOR, FEMALE);
        $this->data['SR_FEMALE_ENTRIES_REMAIN'] = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SENIOR, MALE);
        
        $this->data['REMAINING_ENTRIES']       = LotteryProcess::countRemainingApplications($lotteryTerm);
    }

    public function getCsvColumnsArray()
    {
        return array('lottery applications',
                     'sophomore applications',
                     'junior applications',
                     'senior applications',
                     'lottery assignments',
                     'sophomores assigned',
                     'juniors assigned',
                     'seniors assigned',
                     'remaining invites',
                     'sophomore entires remaining',
                     'junior entires remaining',
                     'senior entries remaining',
                     'outstanding invites',
                     'sohpmore outstanding invites',
                     'junior outstanding invites',
                     'senior outstanding invites',
                     'roommate invites outstanding',
                     'sophomore invites sent',
                     'junior invites sent',
                     'senior invites sent');
    }
    
    public function getCsvRowsArray(){
        $rows = array();
        $rows[] = $this->data;
        return $rows;
    }
    
    public function getData(){
        return $this->data;
    }
    
    public function setTerm($term)
    {
        $this->term = $term;
    }
    
    public function getTerm()
    {
        return $this->term;
    }
}

?>
