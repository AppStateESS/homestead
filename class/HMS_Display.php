<?php

/**
 * Display objects for HMS.
 * Handles display of students, rooms, halls, etc.
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Display
{
 
    function get_system_statistics()
    {
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('is_online', '1');
        $db->addWhere('deleted', '0');
        $num_online = $db->select('count');
        unset($db);

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('is_online', '0');
        $db->addWhere('deleted', '0');
        $num_offline = $db->select('count');
        unset($db);

        $db = &new PHPWS_DB('hms_learning_communities');
        $num_lcs = $db->select('count');
        unset($db);

        $db = &new PHPWS_DB('hms_assignment');
        $db->addWhere('deleted', '0');
        $num_assigned = $db->select('count');
        unset($db);

        $db = &new PHPWS_DB('hms_application');
        $num_applications = $db->select('count');
        unset($db);

        $db = &new PHPWS_DB('hms_learning_community_applications');
        $num_rlc_applications = $db->select('count');
        unset($db);

        $tpl['TITLE']                   = "HMS Overview";
        $tpl['NUM_LCS']                 = $num_lcs;
        $tpl['NUM_ONLINE']              = $num_online;
        $tpl['NUM_OFFLINE']             = $num_offline;
        $tpl['NUM_ASSIGNED']            = $num_assigned;
        $tpl['NUM_APPLICATIONS']        = $num_applications;
        $tpl['NUM_RLC_APPLICATIONS']    = $num_rlc_applications;

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/statistics.tpl');
        return $final;
    }

    function display_greeting()
    {
        $content = "Thank you for logging in to Housing Management System.<br />";
        $content .= "Please choose an operation from the menu to the left.<br />";
        return $content;
    }

    function main()
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
