<?php

/**
 * Maintenance class for HMS
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Maintenance
{

    function HMS_Maintenance()
    {
        $this->error = "";
    }
    
    function set_error_msg($msg)
    {
        $this->error .= $msg;
    }

    function get_error_msg()
    {
        return $this->error;
    }
    
    function purge_data()
    {
        Layout::addPageTitle("Purging Data");

        $content .= "Purging housing applications...<br />";
        $db = &new PHPWS_DB('hms_application');
        $db->delete();
        
        $content .= "Purging Housing Assignments...<br />";
        $db = &new PHPWS_DB('hms_assignment');
        $db->delete();

        $content .= "Purging Deadlines...<br />";
        $db = &new PHPWS_DB('hms_deadlines');
        $db->delete();

        $content .= "Purging Learning Communities...<br />";
        $db = &new PHPWS_DB('hms_learning_communities');
        $db->delete();

        $content .= "Purging Learning Community Applications...<br />";
        $db = &new PHPWS_DB('hms_learning_community_applications');
        $db->delete();

        $content .= "Purging Learning Community Assignments...<br />";
        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->delete();

        $content .= "Purging Learning Community Questions...<br />";
        $db = &new PHPWS_DB('hms_learning_community_questions');
        $db->delete();

        $content .= "Purging residence hall data...<br />";
        $db = &new PHPWS_DB('hms_residence_hall');
        $db->delete();

        $content .= "Purging floor data...<br />";
        $db = &new PHPWS_DB('hms_floor');
        $db->delete();

        $content .= "Purging room data...<br />";
        $db = &new PHPWS_DB('hms_room');
        $db->delete();

        $content .= "Purging bedroom data...<br />";
        $db = &new PHPWS_DB('hms_bedrooms');
        $db->delete();

        $content .= "Purging bed data...<br />";
        $db = &new PHPWS_DB('hms_beds');
        $db->delete();

        $content .= "Purging suite data...<br />";
        $db = &new PHPWS_DB('hms_suite');
        $db->delete();

        $content .= "Done!<br />";
        return $content;
    }

    function show_options()
    {
        Layout::addPageTitle("Comprehensive Maintenance");
        
        $tpl['TERM_LABEL'] = "Term Maintenance";
        if(Current_User::allow('hms', 'create_term') || Current_User::allow('hms', 'admin'))
            $tpl['CREATE_TERM'] = PHPWS_Text::secureLink(_('Create a New Term'), 'hms', array('type'=>'term', 'op'=>'show_create_term'));
        
        if(Current_User::allow('hms', 'term_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['EDIT_TERM']   = PHPWS_Text::secureLink(_('Edit Terms'), 'hms', array('type'=>'term', 'op'=>'show_edit_terms'));
        
        if(Current_User::allow('hms', 'hall_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['HALL_LABEL']  = "Residence Hall Options";

        if(Current_User::allow('hms', 'add_halls') || Current_User::allow('hms', 'admin')) 
            $tpl['ADD_HALL']    = PHPWS_Text::secureLink(_('Add Residence Hall'), 'hms', array('type'=>'hall', 'op'=>'add_hall'));

        //if(Current_User::allow('hms', 'edit_halls')) 
        //    $tpl['EDIT_HALL']   = PHPWS_Text::secureLink(_('Edit Residence Hall'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_edit'));

        if(Current_User::allow('hms', 'delete_halls') || Current_User::allow('hms', 'admin'))
            $tpl['DELETE_HALL'] = PHPWS_Text::secureLink(_('Delete Residence Hall'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_delete'));

        if(Current_User::allow('hms', 'hall_overview') || Current_User::allow('hms', 'admin'))
            $tpl['HALL_OVERVIEW'] = PHPWS_Text::secureLink(_('Get Hall Overview'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_overview'));

        if(Current_User::allow('hms', 'floor_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['FLOOR_LABEL'] = "Floor Options";

        if(Current_User::allow('hms', 'add_floors') || Current_User::allow('hms', 'admin'))
            $tpl['ADD_FLOOR']   = PHPWS_Text::secureLink(_('Add a Floor to a Hall'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_add_floor'));

        if(Current_User::allow('hms', 'edit_floors') || Current_User::allow('hms', 'admin'))
            $tpl['EDIT_FLOOR']   = PHPWS_Text::secureLink(_('Edit a Floor'), 'hms', array('type'=>'floor', 'op'=>'select_hall_for_edit_floor'));
        
        if(Current_User::allow('hms', 'delete_floors') || Current_User::allow('hms', 'admin'))
            $tpl['DELETE_FLOOR']   = PHPWS_Text::secureLink(_('Delete a Floor From a Hall'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_delete_floor'));
       
        if(Current_User::allow('hms', 'room_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['ROOM_LABEL'] = "Room Options";


        if(Current_User::allow('hms', 'add_rooms') || Current_User::allow('hms', 'admin'))
            $tpl['ADD_ROOM'] = PHPWS_Text::secureLink(_('Add a Room'), 'hms', array('type'=>'room', 'op'=>'select_residence_hall_for_add_room'));

        if(Current_User::allow('hms', 'delete_rooms') || Current_User::allow('hms', 'admin')) 
            $tpl['DELETE_ROOM'] = PHPWS_Text::secureLink(_('Delete a Room'), 'hms', array('type'=>'room', 'op'=>'select_residence_hall_for_delete_room'));

        if(Current_User::allow('hms', 'edit_rooms') || Current_User::allow('hms', 'admin'))
            $tpl['EDIT_ROOM'] = PHPWS_Text::secureLink(_('Edit a Room'), 'hms', array('type'=>'room', 'op'=>'select_room_to_edit'));
        
        if(Current_User::allow('hms', 'learning_community_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['LC_LABEL']    = "Learning Community Options";

        if(Current_User::allow('hms', 'add_learning_communities') || Current_User::allow('hms', 'admin'))
            $tpl['ADD_LEARNING_COMMUNITY']      = PHPWS_Text::secureLink(_('Add Learning Community'), 'hms', array('type'=>'rlc', 'op'=>'add_learning_community'));

//        if(Current_User::allow('hms', 'edit_learning_communities') || Current_User::allow('hms', 'admin'))
//            $tpl['EDIT_LEARNING_COMMUNITY']     = PHPWS_Text::secureLink(_('Edit Learning Community'), 'hms', array('type'=>'rlc', 'op'=>'edit_learning_community')) . " &nbsp;*not implemented*";

        if(Current_User::allow('hms', 'delete_learning_communities') || Current_User::allow('hms', 'admin'))
            $tpl['DELETE_LEARNING_COMMUNITY']   = PHPWS_Text::secureLink(_('Delete Learning Community'), 'hms', array('type'=>'rlc', 'op'=>'select_learning_community_for_delete'));

        if(Current_User::allow('hms', 'rlc_applicant_options') || Current_User::allow('hms', 'admin')) 
            $tpl['RLC_APPLICATIONS']    = "RLC Applicant Options";

        if(Current_User::allow('hms', 'assign_rlc_applicants') || Current_User::allow('hms', 'admin')) 
            $tpl['ASSIGN_TO_RLCS']    = PHPWS_Text::secureLink(_('Assign Applicants to RLCs'), 'hms', array('type'=>'rlc', 'op'=>'assign_applicants_to_rlcs'));

/*        if(Current_User::allow('hms', 'rlc_room_assignments') || Current_User::allow('hms', 'admin')) 
            $tpl['RLC_ROOM_ASSIGNMENTS']    = PHPWS_Text::secureLink(_('Assign RLC Members to Rooms'), 'hms', array('type'=>'assignment', 'op'=>'assign_rlc_members_to_rooms'));*/

        if(Current_User::allow('hms', 'search_by_rlc') || Current_User::allow('hms', 'admin'))
            $tpl['SEARCH_BY_RLC'] = PHPWS_Text::secureLink(_('Search by RLC'), 'hms', array('type'=>'rlc', 'op'=>'search_by_rlc'));

        if(Current_User::allow('hms', 'view_rlc_assignments') || Current_User::allow('hms', 'admin'))
            $tpl['VIEW_RLC_ASSIGNMENTS'] = PHPWS_Text::secureLink(_('View RLC Assignments'), 'hms', array('type'=>'rlc', 'op'=>'view_rlc_assignments'));


        if(Current_User::allow('hms', 'student_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['STUDENT_LABEL'] = "Student Maintenance";

        if(Current_User::allow('hms', 'search_for_students') || Current_User::allow('hms', 'admin'))
            $tpl['SEARCH_FOR_STUDENT'] = PHPWS_Text::secureLink(_('Search for a Student'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));

/*
        if(Current_User::allow('hms', 'add_student') || Current_User::allow('hms', 'admin'))
            $tpl['ADD_STUDENT']     = PHPWS_Text::secureLink(_('Add Student'), 'hms', array('type'=>'student', 'op'=>'add_student'));

        if(Current_User::allow('hms', 'edit_student') || Current_User::allow('hms', 'admin'))
            $tpl['EDIT_STUDENT']    = PHPWS_Text::secureLink(_('Edit Student'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));
*/
        if(Current_User::allow('hms', 'deadline_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['DEADLINE_LABEL']  = "Deadline Maintenance";

        if(Current_User::allow('hms', 'edit_deadlines') || Current_User::allow('hms', 'admin')) 
            $tpl['EDIT_DEADLINES']  = PHPWS_Text::secureLink(_('Edit Deadlines'), 'hms', array('type'=>'maintenance', 'op'=>'show_deadlines'));

        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['ASSIGNMENT_LABEL'] = "Assignment Maintenance";

/*        if(Current_User::allow('hms', 'assign_by_floor') || Current_User::allow('hms', 'admin'))
            $tpl['ASSIGN_BY_FLOOR'] = PHPWS_Text::secureLink(_('Assign Entire Floor'), 'hms', array('type'=>'assignment', 'op'=>'begin_by_floor'));*/

        if(Current_User::allow('hms', 'create_assignment') || Current_User::allow('hms', 'admin'))
            $tpl['CREATE_ASSIGNMENT'] = PHPWS_Text::secureLink(_('Assign/Re-assign Student'), 'hms', array('type'=>'assignment', 'op'=>'show_assign_student'));

        if(Current_User::allow('hms', 'delete_assignment') || Current_User::allow('hms', 'admin'))
            $tpl['DELETE_ASSIGNMENT'] = PHPWS_Text::secureLink(_('Unassign Student'), 'hms', array('type'=>'assignment', 'op'=>'show_unassign_student'));

        if(Current_User::allow('hms', 'roommate_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['ROOMMATE_LABEL'] = "Roommate Maintenance";

        if(Current_User::allow('hms', 'create_roommate_group') || Current_User::allow('hms', 'admin'))
            $tpl['CREATE_ROOMMATE_GROUP'] = PHPWS_Text::secureLink(_('Create new roommate group'), 'hms', array('type'=>'roommate', 'op'=>'get_usernames_for_new_grouping'));

        if(Current_User::allow('hms', 'edit_roommate_group') || Current_User::allow('hms', 'admin'))
            $tpl['EDIT_ROOMMATE_GROUP'] = PHPWS_Text::secureLink(_('Edit roommate group'), 'hms', array('type'=>'roommate', 'op'=>'get_username_for_edit_grouping'));

        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['AUTOASSIGN_LABEL'] = "Auto-Assignment";

        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['FILL_QUEUE'] = PHPWS_Text::secureLink(
                _('Fill Assignment Queue'), 'hms',
                array('type'=>'autoassign', 'op'=>'fill'));

        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['VIEW_QUEUE'] = PHPWS_Text::secureLink(
                _('View Assignment Queue'), 'hms',
                array('type'=>'autoassign', 'op'=>'view'));

        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['CLEAR_QUEUE'] = PHPWS_Text::secureLink(
                _('Clear Assignment Queue'), 'hms',
                array('type'=>'autoassign', 'op'=>'clear'));

        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['ASSIGN'] = PHPWS_Text::secureLink(
                _('Auto-Assign'), 'hms',
                array('type'=>'autoassign', 'op'=>'assign'));
        
        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['LETTERS_LABEL'] = _('Letters');
            
        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['GENERATE_UPDATED_LETTERS'] = PHPWS_Text::secureLink(
                _('Generate Updated Letters'), 'hms',
                array('type'=>'letter', 'op'=>'generate'));
            
        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['LIST_LETTERS'] = PHPWS_Text::secureLink(
                _('List Generated Letters'), 'hms',
                array('type'=>'letter', 'op'=>'list'));
            
        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['DOWNLOAD_PDF'] = PHPWS_Text::secureLink(
                _('Download Most Recent PDF'), 'hms',
                array('type'=>'letter', 'op'=>'pdf'));
            
        if(Current_User::allow('hms', 'assignment_maintenance') || Current_User::allow('hms', 'admin'))
            $tpl['DOWNLOAD_CSV'] = PHPWS_Text::secureLink(
                _('Download Most Recent CSV'), 'hms',
                array('type'=>'letter', 'op'=>'csv'));

        PHPWS_Core::initModClass('hms', 'HMS_Process_Assign_Unit.php');
        PHPWS_Core::initModClass('hms', 'HMS_Process_Remove_Unit.php');
        if(Current_User::allow('hms', 'admin')) {
            $tpl['BANNER_LABEL'] = 'Banner Commits ' . (HMS_Process_Unit::assign_queue_enabled() ? 'DISABLED' : 'ENABLED');

            if(HMS_Process_Unit::assign_queue_enabled()) {
                $tpl['ASSIGN_QUEUE'] = PHPWS_Text::secureLink(
                    _('Disable Assignment Queue'), 'hms',
                    array('type'=>'queue', 'queue'=>'assign', 'op'=>'disable'));
            } else {
                $tpl['ASSIGN_QUEUE'] = PHPWS_Text::secureLink(
                    _('Enable Assignment Queue'), 'hms',
                    array('type'=>'queue', 'queue'=>'assign', 'op'=>'enable'));
            }
            //TODO: Process Queues
        }

       $content = PHPWS_Template::process($tpl, 'hms', 'admin/maintenance.tpl');
        return $content;
    }

    function show_deadlines($message = NULL)
    {
        Layout::addPageTitle("Edit Deadlines");
        
        if(!(Current_User::allow('hms', 'edit_deadlines') || Current_User::allow('hms', 'admin'))) {
            exit('you are a bad person that can not edit deadlines.');
        }
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        $form = &new HMS_Form;
        $content = $form->show_deadlines($message);
        return $content;
    }
        
    function save_deadlines()
    {
        if(!(Current_User::authorized('hms', 'edit_deadlines') || Current_User::authorized('hms', 'admin'))) {
            exit('You are a bad person that can not edit deadlines.');
        }
        
        PHPWS_Core::initModClass('hms','HMS_Deadlines.php');
        $deadlines = new HMS_Deadlines();
        
        $slbd   = $_REQUEST['student_login_begin_day'];
        $slbm   = $_REQUEST['student_login_begin_month'];
        $slby   = $_REQUEST['student_login_begin_year'];
        $deadlines->set_student_login_begin_mdy($slbm,$slbd,$slby);

        $sled   = $_REQUEST['student_login_end_day'];
        $slem   = $_REQUEST['student_login_end_month'];
        $sley   = $_REQUEST['student_login_end_year'];
        $deadlines->set_student_login_end_mdy($slem,$sled,$sley);

        $sabd    = $_REQUEST['submit_application_begin_day'];
        $sabm    = $_REQUEST['submit_application_begin_month'];
        $saby    = $_REQUEST['submit_application_begin_year'];
        $deadlines->set_submit_application_begin_mdy($sabm,$sabd,$saby);

        $saed    = $_REQUEST['submit_application_end_day'];
        $saem    = $_REQUEST['submit_application_end_month'];
        $saey    = $_REQUEST['submit_application_end_year'];
        $deadlines->set_submit_application_end_mdy($saem,$saed,$saey);

        $eaed   = $_REQUEST['edit_application_end_day'];
        $eaem   = $_REQUEST['edit_application_end_month'];
        $eaey   = $_REQUEST['edit_application_end_year'];
        $deadlines->set_edit_application_mdy($eaem,$eaed,$eaey);

        $epbd   = $_REQUEST['edit_profile_begin_day'];
        $epbm   = $_REQUEST['edit_profile_begin_month'];
        $epby   = $_REQUEST['edit_profile_begin_year'];
        $deadlines->set_edit_profile_begin_mdy($epbm,$epbd,$epby);

        $eped   = $_REQUEST['edit_profile_end_day'];
        $epem   = $_REQUEST['edit_profile_end_month'];
        $epey   = $_REQUEST['edit_profile_end_year'];
        $deadlines->set_edit_profile_end_mdy($epem,$eped,$epey);
        
        $spbd   = $_REQUEST['search_profiles_begin_day'];
        $spbm   = $_REQUEST['search_profiles_begin_month'];
        $spby   = $_REQUEST['search_profiles_begin_year'];
        $deadlines->set_search_profiles_begin_mdy($spbm,$spbd,$spby);

        $sped   = $_REQUEST['search_profiles_end_day'];
        $spem   = $_REQUEST['search_profiles_end_month'];
        $spey   = $_REQUEST['search_profiles_end_year'];
        $deadlines->set_search_profiles_end_mdy($spem,$sped,$spey);

        $sred   = $_REQUEST['submit_rlc_application_end_day'];
        $srem   = $_REQUEST['submit_rlc_application_end_month'];
        $srey   = $_REQUEST['submit_rlc_application_end_year'];
        $deadlines->set_submit_rlc_application_end_mdy($srem,$sred,$srey);

        $vabd   = $_REQUEST['view_assignment_begin_day'];
        $vabm   = $_REQUEST['view_assignment_begin_month'];
        $vaby   = $_REQUEST['view_assignment_begin_year'];
        $deadlines->set_view_assignment_begin_mdy($vabm,$vabd,$vaby);

        $vaed   = $_REQUEST['view_assignment_end_day'];
        $vaem   = $_REQUEST['view_assignment_end_month'];
        $vaey   = $_REQUEST['view_assignment_end_year'];
        $deadlines->set_view_assignment_end_mdy($vaem,$vaed,$vaey);

        $result = $deadlines->save_deadlines();
        
        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            $message = "Error saving deadlines. Please check the error logs!<br />";
            return HMS_Maintenance::show_deadlines(NULL,$message);
        } else {
            $message = "Deadlines updated successfully!<br />";
            return HMS_Maintenance::show_deadlines($message);
        }
    }

    function main()
    {

        if(!isset($_REQUEST['op'])){
            $op = $_REQUEST['op'];
        }else{
            return HMS_Maintenance::show_options();
        }

        switch($op)
        {
            case 'show_maintenance_options':
                return HMS_Maintenance::show_options();
                break;
            case 'purge':
                return HMS_Maintenance::purge_data();
                break;
            case 'show_deadlines':
                return HMS_Maintenance::show_deadlines();
                break;
            case 'save_deadlines':
                return HMS_Maintenance::save_deadlines();
                break;
            default:
                return HMS_Maintenance::show_options();
                break;
        }
    } 
};
?>
