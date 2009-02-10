<?php

/**
 * Display objects for HMS.
 * Handles display of students, rooms, halls, etc.
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Display
{
 
    public function get_system_statistics()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $term = HMS_Term::get_selected_term();

        $db = new PHPWS_DB('hms_residence_hall');
        $db->addWhere('is_online', '1');
        $db->addWhere('term', $term);
        $num_online = $db->select('count');
        unset($db);

        $db = new PHPWS_DB('hms_residence_hall');
        $db->addWhere('is_online', '0');
        $db->addWhere('term', $term);
        $num_offline = $db->select('count');
        unset($db);

        $db = new PHPWS_DB('hms_learning_communities');
        $num_lcs = $db->select('count');
        unset($db);

        $db = new PHPWS_DB('hms_assignment');
        $db->addWhere('term', $term);
        $num_assigned = $db->select('count');
        unset($db);

        $db = new PHPWS_DB('hms_application');
        $db->addWhere('term', $term);
        $db->addWhere('student_status', 1);
        $num_f_applications = $db->select('count');
        unset($db);

        $db = new PHPWS_DB('hms_application');
        $db->addWhere('term', $term);
        $db->addWhere('student_status', 2);
        $num_t_applications = $db->select('count');
        unset($db);

        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addWhere('term', $term);
        $num_rlc_applications = $db->select('count');
        unset($db);

        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        $lottery_term = PHPWS_Settings::get('hms', 'lottery_term');

        $db = new PHPWS_DB('hms_lottery_entry');
        $db->addWhere('term', $lottery_term);
        $num_lottery_entries = $db->select('count');
        unset($db);

        $tpl['TITLE']                   = "HMS Overview - $term";
        $tpl['NUM_LCS']                 = $num_lcs;
        $tpl['NUM_ONLINE']              = $num_online;
        $tpl['NUM_OFFLINE']             = $num_offline;
        $tpl['NUM_ASSIGNED']            = $num_assigned;
        $tpl['NUM_F_APPLICATIONS']      = $num_f_applications;
        $tpl['NUM_T_APPLICATIONS']      = $num_t_applications;
        $tpl['NUM_RLC_APPLICATIONS']    = $num_rlc_applications;

        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        $lottery_term = PHPWS_Settings::get('hms', 'lottery_term');

        $tpl['LOTTERY_APPLICATIONS']    = $num_lottery_entries;

        $tpl['SOPH_ENTRIES_REMAIN']     = HMS_Lottery::count_remaining_entries_by_class($lottery_term, CLASS_SOPHOMORE);
        $tpl['JR_ENTRIES_REMAIN']       = HMS_Lottery::count_remaining_entries_by_class($lottery_term, CLASS_JUNIOR);
        $tpl['SR_ENTRIES_REMAIN']       = HMS_Lottery::count_remaining_entries_by_class($lottery_term, CLASS_SENIOR);

        $tpl['OUTSTANDING_INVITES']     = HMS_Lottery::count_outstanding_invites($lottery_term, MALE) + HMS_Lottery::count_outstanding_invites($lottery_term, FEMALE);
        $tpl['ROOMMATE_INVITES']        = HMS_Lottery::count_outstanding_roommate_invites($lottery_term);
        $tpl['REMAINING_ENTRIES']       = HMS_Lottery::count_remaining_entries($lottery_term);

        $tpl['SOPH_INVITES']            = HMS_Lottery::count_invites_by_class($lottery_term, CLASS_SOPHOMORE);
        $tpl['JR_INVITES']              = HMS_Lottery::count_invites_by_class($lottery_term, CLASS_JUNIOR);
        $tpl['SR_INVITES']              = HMS_Lottery::count_invites_by_class($lottery_term, CLASS_SENIOR);

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/statistics.tpl');
        return $final;
    }

    public function display_greeting()
    {
        $content = "Thank you for logging in to Housing Management System.<br />";
        $content .= "Please choose an operation from the menu to the left.<br />";
        return $content;
    }

    public function main()
    {
        switch(isset($_REQUEST['op'])?$_REQUEST['op']:'')
        {
            case 'display_system_statistics':
                return HMS_Display::get_system_statistics();
                break;
            default:
                return HMS_Display::display_greeting();
                break;
        }
    }
}
?>
