<?php

  /**
   * @author theFergus <kevin at tux dot appstate dot edu>
   */

function hms_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
        case version_compare($currentVersion, '0.1.3', '<'):
            $files = array();
            $files[] = 'templates/admin/maintenance.tpl';
            PHPWS_Boost::updateFiles($files, 'hms');

            $content[] = _('+ Label and Option on Comp. Maintenance screen to assign RLC applicants');
            $content[] = _('+ Label and Option on Comp. Maintenance screen to view RLC assignments');

        case version_compare($currentVersion, '0.1.1', '<'):
            $result = PHPWS_DB::dropTable('hms_roommate');
            $result = PHPWS_DB::dropTable('hms_student');
            
            $db = & new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/hms/boost/update_0_1_1.sql');
            if (PEAR::isError($result)) {
                return $result;
            }
            
            $files = array();
            $files[] = 'templates/admin/studentList.tpl';
            $files[] = 'templates/admin/roommate_search_results.tpl';
            $files[] = 'templates/admin/display_roommates.tpl';
            $files[] = 'templates/admin/verify_break_roommates.tpl';
            $files[] = 'templates/admin/get_single_username.tpl';
            $files[] = 'templates/admin/get_hall_floor_room.tpl';
            $files[] = 'templates/maintenance.tpl';
            PHPWS_Boost::updateFiles($files, 'hms');
            
            $content[] = _('+ Basic room assignment creation');
            $content[] = _('+ Basic room assignment deletion');
            $content[] = _('+ Search for potential roommates by ASU username');
            $content[] = _('+ Renamed hms_roommate table to hms_roommates');
            $content[] = _('- Removed hms_student table');
    }

    return TRUE;
}

?>
