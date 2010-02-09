<?php

class CreateTermView extends View {

    public function __construct(){}

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        $tpl['TITLE'] = 'Add a New Term';

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }else if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $submitCmd = CommandFactory::getCommand('CreateTerm');
        
        $form = new PHPWS_Form('new_term_form');
        $submitCmd->initForm($form);

        $form->addDropBox('year_drop',HMS_Util::get_years_2yr());
        $form->setLabel('year_drop','Year: ');

        $form->addDropBox('term_drop',Term::getSemesterList());
        $form->setLabel('term_drop','Semester: ');

        $form->addDropBox('copy_drop', array(0 => 'Hall structure only', 1 => 'Hall structure & assignments'));
        $form->setLabel('copy_drop', 'What to copy: ');

        $form->addSubmit('submit','Add Term');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/add_term.tpl');
    }
}

?>