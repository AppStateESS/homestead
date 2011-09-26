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
        
        $lotteryTerm = PHPWS_Settings::get('hms', 'lottery_term');
        
        // Number of entries total
        $this->data['LOTTERY_APPLICATIONS']    = PHPWS_DB::getOne("SELECT count(*) FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id WHERE (hms_new_application.term = $lotteryTerm)");
        
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
        $db->addWhere('lottery', 1);
        $numLotteryAssigned = $db->select('count');
        
        // Assignments
        $this->data['LOTTERY_ASSIGNED']        = $numLotteryAssigned;
        
        // Assignments by class
        $this->data['SOPH_ASSIGNED']           = HMS_Lottery::count_assignments_by_class($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['JR_ASSIGNED']             = HMS_Lottery::count_assignments_by_class($lotteryTerm, CLASS_JUNIOR);
        $this->data['SR_ASSIGNED']             = HMS_Lottery::count_assignments_by_class($lotteryTerm, CLASS_SENIOR);

        // Invites sent
        $this->data['SOPH_INVITES']            = HMS_Lottery::count_invites_by_class($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['JR_INVITES']              = HMS_Lottery::count_invites_by_class($lotteryTerm, CLASS_JUNIOR);
        $this->data['SR_INVITES']              = HMS_Lottery::count_invites_by_class($lotteryTerm, CLASS_SENIOR);

        // Outstanding invites
        $this->data['OUTSTANDING_INVITES']     = HMS_Lottery::count_outstanding_invites($lotteryTerm, MALE) + HMS_Lottery::count_outstanding_invites($lotteryTerm, FEMALE);
        $this->data['SOPH_OUTSTANDING']        = HMS_Lottery::count_outstanding_invites_by_class($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['JR_OUTSTANDING']          = HMS_Lottery::count_outstanding_invites_by_class($lotteryTerm, CLASS_JUNIOR);
        $this->data['SR_OUTSTANDING']          = HMS_Lottery::count_outstanding_invites_by_class($lotteryTerm, CLASS_SENIOR);

        $this->data['ROOMMATE_INVITES']        = HMS_Lottery::count_outstanding_roommate_invites($lotteryTerm);
        
        // Remaining applications
        $this->data['REMAINING_ENTRIES']       = HMS_Lottery::count_remaining_entries($lotteryTerm);
        $this->data['SOPH_ENTRIES_REMAIN']     = HMS_Lottery::count_remaining_entries_by_class($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['JR_ENTRIES_REMAIN']       = HMS_Lottery::count_remaining_entries_by_class($lotteryTerm, CLASS_JUNIOR);
        $this->data['SR_ENTRIES_REMAIN']       = HMS_Lottery::count_remaining_entries_by_class($lotteryTerm, CLASS_SENIOR);
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