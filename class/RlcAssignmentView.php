<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');

class RlcAssignmentView extends View {

    public function show(){
        if( !Current_User::allow('hms', 'view_rlc_applications') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $tags = array();
        $tags['TITLE'] = 'RLC Assignments - ' . Term::toString(Term::getSelectedTerm());
        $tags['SUMMARY']           = HMS_Learning_Community::display_rlc_assignment_summary();
        $tags['DROPDOWN']          = PHPWS_Template::process(HMS_RLC_Application::getDropDown(), 'hms', 'admin/dropdown_template.tpl');
        $tags['ASSIGNMENTS_PAGER'] = HMS_RLC_Application::rlc_application_admin_pager();

        if(isset($success_msg)){
            $tags['SUCCESS_MSG'] = $success_msg;
        }

        if(isset($error_msg)){
            $tags['ERROR_MSG'] = $error_msg;
        }

        $export_form = new PHPWS_Form('export_form');
        //TODO: write command for this...
        $export_form->addHidden('type','rlc');
        $export_form->addHidden('op','rlc_application_export');
        
        $export_form->addDropBox('rlc_list',HMS_Learning_Community::getRLCListAbbr());
        $export_form->addSubmit('submit');
        
        $export_form->mergeTemplate($tags);
        $tags = $export_form->getTemplate();
        
        return PHPWS_Template::process($tags, 'hms', 'admin/make_new_rlc_assignments.tpl');
    }
}
?>
