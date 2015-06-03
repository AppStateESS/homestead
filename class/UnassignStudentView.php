<?php

class UnassignStudentView extends hms\View{

    private $student;

    public function __construct(Student $student = NULL)
    {
        $this->student = $student;
    }

    public function show()
    {
        PHPWS_Core::initCoreClass('Form.php');

        javascript('jquery');
        javascript('modules/hms/assign_student');


        $unassignCmd = CommandFactory::getCommand('UnassignStudent');

        $form = new PHPWS_Form();
        $unassignCmd->initForm($form);

        $form->addText('username');
        if(!is_null($this->student)) {
            $form->setValue('username', $this->student->getUsername());
        }
        $form->addCssClass('username', 'form-control');
        $form->setExtra('username', 'autofocus');

        // Addition of "Unassignment Type"
        $form->addDropBox('unassignment_type', array(
                '-1'                => 'Choose a reason...',
                UNASSIGN_ADMIN      => 'Administrative',
                UNASSIGN_REASSIGN   => 'Re-assign',
                UNASSIGN_CANCEL     => 'Contract Cancellation',
                UNASSIGN_PRE_SPRING => 'Pre-spring room change',
                UNASSIGN_RELEASE    => 'Contract Release'));

        //$form->setMatch('unassignment_type', UNASSIGN_ADMIN);
        $form->setLabel('unassignment_type', 'Unassignment Type: ');
        $form->addCssClass('unassignment_type', 'form-control');

        $form->addText('refund');
        $form->setLabel('refund', 'Refund Percentage');
        $form->setSize('refund', 4);
        $form->setMaxSize('refund', 3);
        $form->addCssClass('refund', 'form-control');

        $form->addTextarea('note');
        $form->setLabel('note', 'Note: ');
        $form->addCssClass('note', 'form-control');

        $tpl = $form->getTemplate();

        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        Layout::addPageTitle("Unassign Student");

        return PHPWS_Template::process($tpl, 'hms', 'admin/unassignStudent.tpl');
    }

}

?>
