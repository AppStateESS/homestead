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

            $content[] = '+ Removed capacity_per_room');
            $content[] = '+ Added bedrooms_per_room');
            $content[] = '+ Added beds_per_bedroom');
            $content[] = '+ Added list of existing halls when adding new halls');
            $content[] = '+ Room assignments working - assignments now by bed instead of room');

        case version_compare($currentVersion, '0.1.8', '<'):
            $files = array();
            $files[] = 'templates/admin/display_learning_community_data.tpl';

            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = '+ Added abbreviation and capacity changes to Add RLC template. They now properly save and delete.';
    }

    return TRUE;
}

?>
