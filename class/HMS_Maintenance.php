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

        $tpl = array();
        
        /********************
         * Term Maintenance *
         ********************/ 
        if(Current_User::allow('hms', 'edit_terms'))
            $tpl['CREATE_TERM'] = PHPWS_Text::secureLink(_('Create a New Term'), 'hms', array('type'=>'term', 'op'=>'show_create_term'));
        
        if(Current_User::allow('hms', 'select_term'))
            $tpl['EDIT_TERM']   = PHPWS_Text::secureLink(_('Edit Terms'), 'hms', array('type'=>'term', 'op'=>'show_edit_terms'));
        

        /**************************
         * Residence Hall Options *
         **************************/
        if(Current_User::allow('hms', 'hall_structure')) 
            $tpl['ADD_HALL']    = PHPWS_Text::secureLink(_('Add Residence Hall'), 'hms', array('type'=>'hall', 'op'=>'add_hall'));

        if(Current_User::allow('hms', 'hall_structure'))
            $tpl['DELETE_HALL'] = PHPWS_Text::secureLink(_('Delete Residence Hall'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_delete'));

        if(Current_User::allow('hms', 'hall_attributes'))
            $tpl['EDIT_HALL']   = PHPWS_Text::secureLink(_('Edit Residence Hall'), 'hms', array('type'=>'hall', 'op'=>'select_hall_to_edit'));

        # TODO: re-evaluate this permissions
        if(Current_User::allow('hms', 'hall_attributes'))
            $tpl['HALL_OVERVIEW'] = PHPWS_Text::secureLink(_('Get Hall Overview'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_overview'));

        /*****************
         * Floor Options *
         *****************/
        if(Current_User::allow('hms', 'floor_structure'))
            $tpl['ADD_FLOOR']   = PHPWS_Text::secureLink(_('Add a Floor to a Hall'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_add_floor'));

        if(Current_User::allow('hms', 'floor_structure'))
            $tpl['DELETE_FLOOR']   = PHPWS_Text::secureLink(_('Delete a Floor From a Hall'), 'hms', array('type'=>'hall', 'op'=>'select_residence_hall_for_delete_floor'));

        if(Current_User::allow('hms', 'floor_attributes'))
            $tpl['EDIT_FLOOR']   = PHPWS_Text::secureLink(_('Edit a Floor'), 'hms', array('type'=>'floor', 'op'=>'show_select_floor'));
        
        
        /****************
         * Room Options *
         ***************/
        if(Current_User::allow('hms', 'room_structure'))
            $tpl['ADD_ROOM'] = PHPWS_Text::secureLink(_('Add a Room'), 'hms', array('type'=>'room', 'op'=>'select_residence_hall_for_add_room'));

        if(Current_User::allow('hms', 'room_structure')) 
            $tpl['DELETE_ROOM'] = PHPWS_Text::secureLink(_('Delete a Room'), 'hms', array('type'=>'room', 'op'=>'select_residence_hall_for_delete_room'));

        if(Current_User::allow('hms', 'room_attributes'))
            $tpl['EDIT_ROOM'] = PHPWS_Text::secureLink(_('Edit a Room'), 'hms', array('type'=>'room', 'op'=>'select_room_to_edit'));

        /***************
         * Bed Options *
         **************/
        if(Current_user::allow('hms', 'bed_attributes'))
            $tpl['EDIT_BED'] = PHPWS_Text::secureLink(_('Edit a Bed'), 'hms', array('type'=>'bed', 'op'=>'select_bed_to_edit'));

        /***************
         * RLC Options *
         ***************/
        if(Current_User::allow('hms', 'learning_community_maintenance'))
            $tpl['ADD_LEARNING_COMMUNITY']      = PHPWS_Text::secureLink(_('Add Learning Community'), 'hms', array('type'=>'rlc', 'op'=>'add_learning_community'));

        if(Current_User::allow('hms', 'learning_community_maintenance'))
            $tpl['DELETE_LEARNING_COMMUNITY']   = PHPWS_Text::secureLink(_('Delete Learning Community'), 'hms', array('type'=>'rlc', 'op'=>'select_learning_community_for_delete'));

//        if(Current_User::allow('hms', 'learning_community_maintenance'))
//            $tpl['EDIT_LEARNING_COMMUNITY']     = PHPWS_Text::secureLink(_('Edit Learning Community'), 'hms', array('type'=>'rlc', 'op'=>'edit_learning_community')) . " &nbsp;*not implemented*";

        if(Current_User::allow('hms', 'view_rlc_applications')) 
            $tpl['ASSIGN_TO_RLCS']    = PHPWS_Text::secureLink(_('Assign Applicants to RLCs'), 'hms', array('type'=>'rlc', 'op'=>'assign_applicants_to_rlcs'));

/*        if(Current_User::allow('hms', 'rlc_room_assignments')) 
            $tpl['RLC_ROOM_ASSIGNMENTS']    = PHPWS_Text::secureLink(_('Assign RLC Members to Rooms'), 'hms', array('type'=>'assignment', 'op'=>'assign_rlc_members_to_rooms'));*/

        if(Current_User::allow('hms', 'view_rlc_members'))
            $tpl['SEARCH_BY_RLC'] = PHPWS_Text::secureLink(_('View RLC Members by RLC'), 'hms', array('type'=>'rlc', 'op'=>'search_by_rlc'));

        if(Current_User::allow('hms', 'view_rlc_room_assignments'))
            $tpl['VIEW_RLC_ASSIGNMENTS'] = PHPWS_Text::secureLink(_('View RLC Room Assignments'), 'hms', array('type'=>'rlc', 'op'=>'view_rlc_assignments'));

        /***********************
         * Student Maintenance *
         ***********************/
        if(Current_User::allow('hms', 'search'))
            $tpl['SEARCH_FOR_STUDENT'] = PHPWS_Text::secureLink(_('Search for a Student'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));

/*
        if(Current_User::allow('hms', 'add_student'))
            $tpl['ADD_STUDENT']     = PHPWS_Text::secureLink(_('Add Student'), 'hms', array('type'=>'student', 'op'=>'add_student'));

        if(Current_User::allow('hms', 'edit_student'))
            $tpl['EDIT_STUDENT']    = PHPWS_Text::secureLink(_('Edit Student'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));
*/

        /************************
         * Deadline Maintenance *
         ************************/
        if(Current_User::allow('hms', 'view_deadlines')) 
            $tpl['EDIT_DEADLINES']  = PHPWS_Text::secureLink(_('View/Edit Deadlines'), 'hms', array('type'=>'deadlines', 'op'=>'show_edit_deadlines'));
            
        /***************
         * Assignments *
         ***************/
/*        if(Current_User::allow('hms', 'assign_by_floor'))
            $tpl['ASSIGN_BY_FLOOR'] = PHPWS_Text::secureLink(_('Assign Entire Floor'), 'hms', array('type'=>'assignment', 'op'=>'begin_by_floor'));*/

        if(Current_User::allow('hms', 'assignment_maintenance'))
            $tpl['CREATE_ASSIGNMENT'] = PHPWS_Text::secureLink(_('Assign/Re-assign Student'), 'hms', array('type'=>'assignment', 'op'=>'show_assign_student'));

        if(Current_User::allow('hms', 'assignment_maintenance'))
            $tpl['DELETE_ASSIGNMENT'] = PHPWS_Text::secureLink(_('Unassign Student'), 'hms', array('type'=>'assignment', 'op'=>'show_unassign_student'));
        
        /*************
         * Roommates *
         ************/
        if(Current_User::allow('hms', 'create_roommate_group'))
            $tpl['CREATE_ROOMMATE_GROUP'] = PHPWS_Text::secureLink(_('Create new roommate group'), 'hms', array('type'=>'roommate', 'op'=>'show_admin_create_roommate_group'));

        if(Current_User::allow('hms', 'edit_roommate_group'))
            $tpl['EDIT_ROOMMATE_GROUP'] = PHPWS_Text::secureLink(_('Edit roommate group'), 'hms', array('type'=>'roommate', 'op'=>'show_confirmed_roommates'));

        /*******************
         * Auto-assignment *
         *******************/
        if(Current_User::allow('hms', 'assignment_maintenance'))
            $tpl['ASSIGN'] = PHPWS_Text::secureLink(
                _('Auto-Assign'), 'hms',
                array('type'=>'autoassign', 'op'=>'assign'));
        
        /***********
         * Letters *
         ***********/
        if(Current_User::allow('hms', 'assignment_maintenance'))
            $tpl['GENERATE_UPDATED_LETTERS'] = PHPWS_Text::secureLink(
                _('Generate Updated Letters'), 'hms',
                array('type'=>'letter', 'op'=>'generate'));
            
        if(Current_User::allow('hms', 'assignment_maintenance'))
            $tpl['LIST_LETTERS'] = PHPWS_Text::secureLink(
                _('List Generated Letters'), 'hms',
                array('type'=>'letter', 'op'=>'list'));
            
        if(Current_User::allow('hms', 'assignment_maintenance'))
            $tpl['DOWNLOAD_PDF'] = PHPWS_Text::secureLink(
                _('Download Most Recent PDF'), 'hms',
                array('type'=>'letter', 'op'=>'pdf'));
            
        if(Current_User::allow('hms', 'assignment_maintenance'))
            $tpl['DOWNLOAD_CSV'] = PHPWS_Text::secureLink(
                _('Download Most Recent CSV'), 'hms',
                array('type'=>'letter', 'op'=>'csv'));
            
        /*****************
         * Suite Options *
         *****************/
        if(Current_User::allow('hms', 'suite_attributes')){
            $tpl['EDIT_SUITE'] = PHPWS_Text::secureLink(
                _('Edit Suite'), 'hms',
                array('type'=>'suite', 'op'=>'show_select_suite'));
        }
        
        /****************
         * Movein times *
         ****************/

        if(Current_User::allow('hms', 'edit_movein_times')){
            $tpl['EDIT_MOVEIN_TIMES'] = PHPWS_Text::secureLink(
                _('Edit Move-in Times'), 'hms',
                array('type'=>'movein', 'op'=>'show_edit_movein_times'));
        }

        /****************
         * Activity Log *
         ****************/

        if(Current_User::allow('hms', 'view_activity_log')) {
            $tpl['VIEW_ACTIVITY_LOG'] = PHPWS_Text::secureLink(
                _('View Activity Log'), 'hms',
                array('type'=>'activity_log', 'op'=>'view'));
        }

        $content = PHPWS_Template::process($tpl, 'hms', 'admin/maintenance.tpl');
        return $content;
    }

    function main()
    {

        if(isset($_REQUEST['op'])){
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
            default:
                return HMS_Maintenance::show_options();
                break;
        }
    } 
};
?>
