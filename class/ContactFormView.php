<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class ContactFormView extends hms\View{

    public function show()
    {
        $username = UserStatus::getUsername();
        $currentTerm = Term::getCurrentTerm();
        $student = StudentFactory::getStudentByUsername($username, $currentTerm);
        $applicationTerm = $student->getApplicationTerm();

        $tpl = array();
        $tpl['TITLE'] = 'Contact Form';

        $form = new PHPWS_Form();

        $form->addText('name');
        $form->setLabel('name', 'Name');

        $form->addText('email');
        $form->setLabel('email', 'Email Address');

        $form->addText('phone');
        $form->setLabel('phone', 'Phone number');

        $form->addDropBox('stype', array('F'=>'New Freshmen', 'T'=>'Transfer', 'C'=>'Returning'));
        $form->setLabel('stype', 'Classification');

        $form->addTextArea('comments');
        $form->setLabel('comments', 'Comments and/or what you were trying to do');

        javascript('modules/hms/autoFocus', array('ELEMENT'=>$form->getId('name')));
        $form->addSubmit('Submit');

        $form->mergeTemplate($tpl);

        $cmd = CommandFactory::getCommand('SubmitContactForm');
        $cmd->setUsername($username);
        $cmd->setApplicationTerm($applicationTerm);
        $cmd->setStudentType($student->getType());
        $cmd->initForm($form);

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/contact_page.tpl');
    }
}
?>
