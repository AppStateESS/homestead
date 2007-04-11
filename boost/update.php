<?php

  /**
   * @author theFergus <kevin at tux dot appstate dot edu>
   */

function hms_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '0.1.2', '<'):
            $db = & new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/0_1_2.sql');
            if (PEAR::isError($result)) {
                return $result;
            }
            
            $files = array();
            $files[] = 'templates/student/rlc_signup_form_page2.tmp';
            
            PHPWS_Boost::updateFiles($files, 'hms');
            
            $content[] = _('+ RLC application form template');
            $content[] = _('+ RLC application table');
        
        case version_compare($currentVersion, '0.1.3', '<'):
            $files = array();
            $files[] = 'templates/student/rlc_signup_confirmation.tpl';
            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = _('+ Complete system for RLC applications');
        
        case version_compare($currentVersion, '0.1.4', '<'):
            $db = & new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/0_1_4.sql');
            if (PEAR::isError($result)) {
                return $result;
            }

            $files = array();
            $files[] = 'templates/admin/display_final_rlc_assignments.tpl';
            $files[] = 'templates/admin/display_rlc_student_detail_form_questions.tpl';
            $files[] = 'templates/admin/make_new_rlc_assignments.tpl';
            $files[] = 'templates/admin/make_new_rlc_assignments_summary.tpl';
            $files[] = 'templates/admin/display_rlc_student_detail.tpl';
            $files[] = 'templates/admin/deadlines.tpl';
            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = _('+ RLC administration templates');
            $content[] = _('+ Deadline for Questionnaire replaced by deadlines for Profile and Application');
            $content[] = _('+ Deadline added for editing applications');
            $content[] = _('+ Deadline added for submitting RLC applications'); 

        case version_compare($currentVersion, '0.1.5', '<'):
            $db = &new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/0_1_5.sql');
            if(PEAR::isError($result)) {
                return $result;
            }

            $files      = array();
            $files[]    = 'templates/admin/deadlines.tpl';
            $files[]    = 'templates/admin/statistics.tpl';
            $files[]    = 'templates/student/application_search.tpl';
            $files[]    = 'templates/student/application_search_pager.tpl';
            $files[]    = 'templates/student/application_search_results.tpl';
            $files[]    = 'templates/student/contract.tpl';
            $files[]    = 'templates/student/student_application.tpl';
            $files[]    = 'templates/student/student_application_combined.tpl';
            $files[]    = 'templates/student/student_application_redo.tpl';

            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = _('+ Fixed RLC deadline bug in deadlines.tpl');
            $content[] = _('+ Added Number of People Assigned');
            $content[] = _('+ Added Number of Applications Received');
            $content[] = _('+ Added Number of Learning Community Applications Received');
            $content[] = _('+ Refactored questionnaire references to application');
            $content[] = _('+ Added the contract verbage for when a student first logs in');
            $content[] = _('+ Completed Housing applications now go straight into the RLC application if the student said they were interested');
            $content[] = _('+ Added link to allow students to go to the RLC application on first login as soon as they complete an application');
            $content[] = _('+ Added link to the pdf of the contract for students that want to print it out');

        case version_compare($currentVersion, '0.1.6', '<'):
            $db = &new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/0_1_6.sql');
            if(PEAR::isError($result)) {
                return $result;
            }
            
            $files      = array();
            $files[]    = 'templates/admin/maintenance.tpl';
            $files[]    = 'templates/misc/login.tpl';

            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = _('+ Modifying permissions for RLC admins to approve members and assign to rooms');
            $content[] = _('+ Added verbage for students to see before they login');

        case version_compare($currentVersion, '0.1.7', '<'):
            $db = &new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/0_1_7.sql');
            if(PEAR::isError($result)) {
                return $result;
            }

            $files   = array();
            $files[] = 'templates/admin/make_new_rlc_assignments_summary.tpl';
            $files[] = 'templates/admin/rlc_assignments_page.tpl';
            $files[] = 'templates/admin/add_floor.tpl';
            $files[] = 'templates/admin/display_room_data.tpl';
            $files[] = 'templates/admin/display_hall_data.tpl';
            $files[] = 'templates/admin/get_hall_floor_room.tpl';

            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = '+ Removed capacity_per_room';
            $content[] = '+ Added bedrooms_per_room';
            $content[] = '+ Added beds_per_bedroom';
            $content[] = '+ Added list of existing halls when adding new halls';
            $content[] = '+ Room assignments working - assignments now by bed instead of room';

        case version_compare($currentVersion, '0.1.8', '<'):
            $db = &new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/0_1_8.sql');
            if(PEAR::isError($result)) {
                return $result;
            }

            $files = array();
            $files[] = 'templates/admin/display_learning_community_data.tpl';
            $files[] = 'templates/admin/maintenance.tpl';
            $files[] = 'templates/admin/display_hall_data.tpl';
            $files[] = 'templates/admin/add_floor.tpl';
            $files[] = 'templates/admin/display_floor_data.tpl';
            $files[] = 'templates/student/student_application.tpl';
            $files[] = 'templates/admin/select_room_for_delete.tpl';
            $files[] = 'templates/admin/display_room_data.tpl';
            $files[] = 'templates/admin/verify_delete_room.tpl';
            $files[] = 'templates/admin/select_floor_for_delete_room.tpl';
            $files[] = 'templates/misc/side_thingie.tpl';

            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = '+ Added abbreviation and capacity changes to Add RLC template. They now properly save and delete.';
            $content[] = '+ Deleting a building now deletes the bedrooms and beds in that building.';
            $content[] = '+ Hid Edit Building temporarily. Bedroom/bed maintenance needs to be finished first.';
            $content[] = '+ Editing a floor works again. Can not delete/add rooms from floor maintenance, must go through room menu.';
            $content[] = '+ Removed gender option from student_application.tpl';

        case version_compare($currentVersion, '0.1.9', '<'):
            $db = &new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/0_1_9.sql');
            if(PEAR::isError($result)) {
                return $result;
            }

            $files = array();
            $files[] = 'templates/admin/maintenance.tpl';

            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = '+ Sync\'d with the current live release.';
       
        case version_compare($currentVersion, '0.1.10', '<'):
            $files = array();
            $files[] = 'templates/admin/assign_floor.tpl';
            $files[] = 'templates/admin/bed_and_id.tpl';
            $files[] = 'templates/admin/get_hall_floor.tpl';
            $files[] = 'templates/admin/maintenance.tpl';
            $files[] = 'templates/admin/select_floor_for_edit.tpl';
            $files[] = 'templates/admin/select_residence_hall.tpl';
            $files[] = 'templates/admin/select_room_for_edit.tpl';
            $fiels[] = 'templates/student/student_application.tpl';
            
            PHPWS_Boost::updateFiles($files, 'hms');
            
            $content[] = '+ Changed templates regarding editing/deleting rooms and floors to be more user friendly';
            $content[] = '+ Changed to version 0.1.10 to get all dev sites and production site in sync';
            $content[] = '+ Changed HMS_Room so beds are deleted manually instead of through a db object';
            $content[] = '+ Added mechanism to handle mass assignment of an entire floor';
            $content[] = '+ Added student\'s name and gender to student application template';
            $content[] = '+ All locations where usernames are saved have been extended to size 32';
            $content[] = '+ All RLC question response lengths have been extended to 2048 characters';
            $content[] = '+ WSDL modified to reflect change in Web Services server location';

        case version_compare($currentVersion, '0.1.11', '<'):
            $content[] = '+ Fixed minor glitch where assignment by room range was pulling rooms incorrectly (did not take floor number into account)';

        case version_compate($currentVersion, '0.1.12', '<'):
            $files = array();
            $files[] = 'templates/student/contract.tpl'

            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = '+ Contract text now shows in a scrollable iframe';
            $content[] = '+ PDF of the contract now opens in a new tab/window';
            $content[] = '+ Link to Acrobat download, opens in new tab/window';
            $content[] = '+ Added link to a FAQ page. We need to make sure there *is* a FAQ page.';

    }

    return TRUE;
}

?>
