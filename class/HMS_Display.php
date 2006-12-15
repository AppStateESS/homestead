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
        $all = $db->select();
        $num_online = sizeof($all);
        unset($db);
        unset($all);

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addWhere('is_online', '0');
        $db->addWhere('deleted', '0');
        $all = $db->select();
        $num_offline = sizeof($all);
        unset($db);
        unset($all);

        $db = &new PHPWS_DB('hms_learning_communities');
        $all = $db->select();
        $db->addWhere('deleted', '0');
        $num_lcs = sizeof($all);
        unset($db);
        unset($all);

        $tpl['TITLE']   = "HMS Overview";
        $tpl['NUM_ONLINE']  = $num_online;
        $tpl['NUM_OFFLINE'] = $num_offline;
        $tpl['NUM_LCS']     = $num_lcs;
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
        switch($_REQUEST['op'])
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
