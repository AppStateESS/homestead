<?php

class TermsAgreementView extends hms\View {

    private $term;
    private $submitCommand;
    private $student;

    public function __construct($term, Command $submitCommand, Student $student)
    {
        $this->term = $term;
        $this->submitCommand = $submitCommand;
        $this->student = $student;
    }

    public function show()
    {

        $form = new PHPWS_Form;
        $this->submitCommand->initForm($form);

        $tpl = $form->getTemplate();

        $tpl['TERM'] = Term::toString($this->term);
        $tpl['DOCUSIGN_BEGIN_CMD'] = $this->submitCommand->getURI();

        if($this->student->isUnder18()){
        	$tpl['UNDER_18'] = '';
        }

        javascript('jquery');
        Layout::addPageTitle("License Agreement");

        return PHPWS_Template::process($tpl, 'hms', 'student/contract.tpl');
    }
}
