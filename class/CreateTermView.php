<?php

namespace Homestead;

/**
 * @author Jeremy Booker
 * @package hms
 */
class CreateTermView extends View {

    public function __construct(){}

    public function show()
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'edit_terms')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit terms.');
        }
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        javascript('jquery');
        javascript('modules/hms/newTermCopyPick');

        $tpl = array();

        $tpl['TITLE'] = 'Add a New Term';

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }else if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $submitCmd = CommandFactory::getCommand('CreateTerm');

        $form = new \PHPWS_Form('new_term_form');
        $submitCmd->initForm($form);

        $form->addDropBox('from_term', Term::getTermsAssoc());
        $form->setLabel('from_term', 'Copy from:');
        $form->addCssClass('from_term', 'form-control');

        $form->addDropBox('year_drop',HMS_Util::get_years_2yr());
        $form->setLabel('year_drop','Year: ');
        $form->addCssClass('year_drop', 'form-control');

        $form->addDropBox('term_drop',Term::getSemesterList());
        $form->setLabel('term_drop','Semester: ');
        $form->addCssClass('term_drop', 'form-control');

        $vars = array('struct' => 'Hall structure', 'assign' => 'Assignments (and Meal Plans)', 'role' => 'Roles');
        $form->addCheckAssoc('copy_pick', $vars);
        $form->addCssClass('', 'form-control');
        $tpl['COPY_PICK_LABEL'] = 'What to copy:';


        $form->addSubmit('submit','Add Term');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Create Term");

        return \PHPWS_Template::process($tpl, 'hms', 'admin/add_term.tpl');
    }
}
