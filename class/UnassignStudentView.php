<?php

PHPWS_Core::initModClass('hms', 'View.php');

class UnassignStudentView extends View {

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
		Layout::addStyle('hms', 'css/autosuggest2.css');


		$unassignCmd = CommandFactory::getCommand('UnassignStudent');

        $form = new PHPWS_Form();
        $unassignCmd->initForm($form);

        $form->addText('username');
        if(!is_null($this->student)) {
            $form->setValue('username', $this->student->getUsername());
        }

        $var = array('ELEMENT' => $form->getId('username'));
        javascript('modules/hms/autoFocus', $var);

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