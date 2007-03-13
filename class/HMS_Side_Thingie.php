<?php

define('HMS_SIDE_STUDENT_MIN',      1);
define('HMS_SIDE_STUDENT_AGREE',    1);
define('HMS_SIDE_STUDENT_APPLY',    2);
define('HMS_SIDE_STUDENT_RLC',      3);
define('HMS_SIDE_STUDENT_PROFILE',  4);
define('HMS_SIDE_STUDENT_ROOMMATE', 5);
define('HMS_SIDE_STUDENT_VERIFY',   6);
define('HMS_SIDE_STUDENT_MAX',      6);

class HMS_Side_Thingie {
    
    function show($step) {
        $template = array();
        switch($step) {
            case HMS_SIDE_STUDENT_AGREE:
            case HMS_SIDE_STUDENT_APPLY:
            case HMS_SIDE_STUDENT_RLC:
            case HMS_SIDE_STUDENT_PROFILE:
            case HMS_SIDE_STUDENT_ROOMMATE:
            case HMS_SIDE_STUDENT_VERIFY:
                $template['TITLE'] = _('Application Progress');
                for($i = HMS_SIDE_STUDENT_MIN;
                        $i <= HMS_SIDE_STUDENT_MAX; $i++) {
                    $template['progress'][$i - HMS_SIDE_STUDENT_MIN]
                        [$i == $step ? 'STEP_CURRENT' : 'STEP_TOGO'] = "Schwiggity $i";
                }
                break;
        }

        $page = PHPWS_Template::process($template, 'hms', 'misc/side_thingie.tpl');
        Layout::add($page, 'hms', 'default');
    }

}
