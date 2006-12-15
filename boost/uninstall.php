<?php

/**
 * Uninstall file for hms
 */

function hms_uninstall(&$content)
{
    $result = PHPWS_DB::dropTable('hms_student');
    $result = PHPWS_DB::dropTable('hms_residence_hall');
    $result = PHPWS_DB::dropTable('hms_floor');
    $result = PHPWS_DB::dropTable('hms_room');
    $result = PHPWS_DB::dropTable('hms_hall_communities');
    $result = PHPWS_DB::dropTable('hms_learning_communities');
    $result = PHPWS_DB::dropTable('hms_pricing_tiers');
    $result = PHPWS_DB::dropTable('hms_deadlines');
    $result = PHPWS_DB::dropTable('hms_questionnaire');
    $content[] = _('HMS tables removed.');
    return TRUE;
}

?>
