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

        $var = array('ELEMENT' => $form->getId('username'));
        javascript('modules/hms/autoFocus', $var);

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

        $form->addText('refund');
        $form->setLabel('refund', 'Refund Percentage');
        $form->setSize('refund', 4);
        $form->setMaxSize('refund', 3);

        $form->addTextarea('note');
        $form->setLabel('note', 'Note: ');

        $form->addSubmit('submit', _('Unassign Student'));

        $tpl = $form->getTemplate();

        $tpl['TERM'] = Term::getPrintableSelectedTerm();

        Layout::addPageTitle("Unassign Student");

        return PHPWS_Template::process($tpl, 'hms', 'admin/unassign_student.tpl');
    }

}

?>
