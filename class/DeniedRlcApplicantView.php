<?php

PHPWS_Core::initModClass('hms', 'View.php');

class DeniedRlcApplicantView extends hms\View {

    public function show(){
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $tpl = array();

        $tpl['TITLE'] = "Denied RLC Applications - " . Term::toString(Term::getSelectedTerm());
        $tpl['DENIED_PAGER'] = HMS_RLC_Application::denied_pager();

        if(isset($success_msg)){
            $tpl['SUCCESS_MSG'] = $success_msg;
        }

        if(isset($error_msg)){
            $tpl['ERROR_MSG'] = $error_msg;
        }

        Layout::addPageTitle("Denied RLC Applications");

        return PHPWS_Template::process($tpl, 'hms', 'admin/view_denied_rlc_applications.tpl');
    }
}
?>
