<?php

/**
 * Uninstall file for hms
 */

function hms_uninstall(&$content)
{
    /* Replaced by uninstall.sql
#    PHPWS_DB::begin();
    $result[] = PHPWS_DB::dropTable('hms_pricing_tiers');
    $result[] = PHPWS_DB::dropTable('hms_hall_communities');
    $result[] = PHPWS_DB::dropTable('hms_learning_community_questions');
    $result[] = PHPWS_DB::dropTable('hms_learning_communities'); # must drop this after learning_community_applications and learning_community_questions becase of foreign keys
    $result[] = PHPWS_DB::dropTable('hms_learning_community_assignment');
    $result[] = PHPWS_DB::dropTable('hms_learning_community_floors');
    $result[] = PHPWS_DB::dropTable('hms_roommates');
    $result[] = PHPWS_DB::dropTable('hms_roommate_approval');
    $result[] = PHPWS_DB::dropTable('hms_student');
    $result[] = PHPWS_DB::dropTable('hms_student_profiles');
    $result[] = PHPWS_DB::dropTable('hms_cached_student_info');
    $result[] = PHPWS_DB::dropTable('hms_pending_assignment');
    $result[] = PHPWS_DB::dropTable('hms_activity_log');
    $result[] = PHPWS_DB::dropTable('hms_questionnaire');
    $result[] = PHPWS_DB::dropTable('hms_bedrooms');
    $result[] = PHPWS_DB::dropTable('hms_roommate_hashes');
    $result[] = PHPWS_DB::dropTable('hms_suite');
    $result[] = PHPWS_DB::dropTable('hms_assignment');
    $result[] = PHPWS_DB::dropTable('hms_bed');
    $result[] = PHPWS_DB::dropTable('hms_room');
    $result[] = PHPWS_DB::dropTable('hms_floor');
    $result[] = PHPWS_DB::dropTable('hms_residence_hall');
    $result[] = PHPWS_DB::dropTable('hms_deadlines');
    $result[] = PHPWS_DB::dropTable('hms_movein_time');
    $result[] = PHPWS_DB::dropTable('hms_assignment_queue');
    $result[] = PHPWS_DB::dropTable('hms_application');
    $result[] = PHPWS_DB::dropTable('hms_learning_community_applications');
    $result[] = PHPWS_DB::dropTable('hms_term');

    foreach($result as $r) {
        if(PEAR::isError($r)) {
#            PHPWS_DB::rollback();
            return $r;
        }
    }
    
    $content[] = _('HMS tables removed.');
#    PHPWS_DB::commit();
    */
    return TRUE;
}

?>
