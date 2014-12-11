<?php

/**
 * PinDisabledView
 *
 * Shows the student a notification that his/her PIN is disabled, with instructions on how to re-enable it.
 *
 * @author Jeremy Booker
 * @package HMS
 */

class PinDisabledView extends homestead\View {

    public function show()
    {
        $tpl = array();
        return PHPWS_Template::process($tpl, 'hms', 'student/pinDisabled.tpl');
    }
}

?>