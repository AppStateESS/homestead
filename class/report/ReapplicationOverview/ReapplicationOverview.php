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
    const shortName    = 'ReapplicationOverview';
    const category     = 'Applications';

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

        /*******************************
         * Gross number of application *
         *******************************/
        $this->data['LOTTERY_APPS'] = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm);

        $this->data['SOPH_APPS']    = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['SOPH_M_APPS']  = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_SOPHOMORE, MALE);
        $this->data['SOPH_F_APPS']  = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_SOPHOMORE, FEMALE);

        $this->data['JR_APPS']      = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_JUNIOR);
        $this->data['JR_M_APPS']    = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_JUNIOR, MALE);
        $this->data['JR_F_APPS']    = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_JUNIOR, FEMALE);

        $this->data['SR_APPS']      = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_SENIOR);
        $this->data['SR_M_APPS']    = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_SENIOR, MALE);
        $this->data['SR_F_APPS']    = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, CLASS_SENIOR, FEMALE);

        $this->data['M_APPS']       = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, null, MALE);
        $this->data['F_APPS']       = LotteryProcess::countGrossApplicationsByClassGender($lotteryTerm, null, FEMALE);

        /********************
         * Net Applications *
         ********************/

        $this->data['NET_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, null, null);

        $this->data['NET_SO_APPS']   = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['NET_SO_M_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_SOPHOMORE, MALE);
        $this->data['NET_SO_F_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_SOPHOMORE, FEMALE);

        $this->data['NET_JR_APPS']   =  LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_JUNIOR);
        $this->data['NET_JR_M_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_JUNIOR, MALE);
        $this->data['NET_JR_F_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_JUNIOR, FEMALE);

        $this->data['NET_SR_APPS']   = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_SENIOR);
        $this->data['NET_SR_M_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_SENIOR, MALE);
        $this->data['NET_SR_F_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, CLASS_SENIOR, FEMALE);

        $this->data['NET_M_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, null, MALE);
        $this->data['NET_F_APPS'] = LotteryProcess::countNetAppsByClassGender($lotteryTerm, null, FEMALE);

        /****************
         * Invites Sent *
         ****************/
        $this->data['SO_INVITES']    = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['SO_M_INVITES']  = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SOPHOMORE, MALE);
        $this->data['SO_F_INVITES']  = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SOPHOMORE, FEMALE);


        $this->data['JR_INVITES']    = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_JUNIOR);
        $this->data['JR_M_INVITES']  = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_JUNIOR, MALE);
        $this->data['JR_F_INVITES']  = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_JUNIOR, FEMALE);


        $this->data['SR_INVITES']    = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SENIOR);
        $this->data['SR_M_INVITES']  = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SENIOR, MALE);
        $this->data['SR_F_INVITES']  = LotteryProcess::countInvitesByClassGender($lotteryTerm, CLASS_SENIOR, FEMALE);


        //TODO make this based on lottery assignment reason
        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('term', $lotteryTerm);
        $db->addWhere('reason', 'lottery');
        $numLotteryAssigned = $db->select('count');


        /***********************
         * Outstanding Invites *
         */
        $this->data['ROOMMATE_INVITES']        = LotteryProcess::countOutstandingRoommateInvites($lotteryTerm);

        $this->data['PENDING_SOPH_INVITES']    = LotteryProcess::countOutstandingInvites($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['PENDING_JR_INVITES']      = LotteryProcess::countOutstandingInvites($lotteryTerm, CLASS_JUNIOR);
        $this->data['PENDING_SR_INVITES']      = LotteryProcess::countOutstandingInvites($lotteryTerm, CLASS_SENIOR);

        // Assignments
        $this->data['LOTTERY_ASSIGNED']        = $numLotteryAssigned;

        // Assignments by class
        $this->data['SOPH_ASSIGNED']           = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SOPHOMORE);
        $this->data['SOPH_MALE_ASSIGNED']      = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SOPHOMORE, MALE);
        $this->data['SOPH_FEMALE_ASSIGNED']    = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SOPHOMORE, FEMALE);

        $this->data['JR_ASSIGNED']             = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_JUNIOR);
        $this->data['JR_MALE_ASSIGNED']        = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_JUNIOR, MALE);
        $this->data['JR_FEMALE_ASSIGNED']      = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_JUNIOR, FEMALE);

        $this->data['SR_ASSIGNED']             = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SENIOR);
        $this->data['SR_MALE_ASSIGNED']        = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SENIOR, MALE);
        $this->data['SR_FEMALE_ASSIGNED']      = LotteryProcess::countLotteryAssignedByClassGender($lotteryTerm, CLASS_SENIOR, FEMALE);

        /***************************************
         * Remaining unaffiliated applications *
         ***************************************/
        $this->data['SO_M_ENTRIES_REMAIN']   = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SOPHOMORE, MALE);
        $this->data['SO_F_ENTRIES_REMAIN'] = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SOPHOMORE, FEMALE);
        $this->data['SO_ENTRIES_REMAIN']        = $this->data['SO_M_ENTRIES_REMAIN'] + $this->data['SO_F_ENTRIES_REMAIN'];

        $this->data['JR_M_ENTRIES_REMAIN']     = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_JUNIOR, MALE);
        $this->data['JR_F_ENTRIES_REMAIN']   = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_JUNIOR, FEMALE);
        $this->data['JR_ENTRIES_REMAIN']          = $this->data['JR_M_ENTRIES_REMAIN'] + $this->data['JR_F_ENTRIES_REMAIN'];

        $this->data['SR_M_ENTRIES_REMAIN']     = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SENIOR, MALE);
        $this->data['SR_F_ENTRIES_REMAIN']   = LotteryProcess::countRemainingApplicationsByClassGender($lotteryTerm, CLASS_SENIOR, FEMALE);
        $this->data['SR_ENTRIES_REMAIN']          = $this->data['SR_M_ENTRIES_REMAIN'] + $this->data['SR_F_ENTRIES_REMAIN'];

        $this->data['REMAINING_ENTRIES']          = LotteryProcess::countRemainingApplications($lotteryTerm);
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
