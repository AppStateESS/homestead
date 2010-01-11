<?php


class TermsAgreementView extends View {
	
	private $term;
	private $submitCommand;
	
	public function __construct($term, Command $submitCommand)
	{
		$this->term = $term;
		$this->submitCommand = $submitCommand;
	}
	
	public function show() 
	{
		
		$form = new PHPWS_Form;
		$this->submitCommand->initForm($form);
        
        $form->addSubmit('begin', _('I Agree'));
        $form->setExtra('begin', 'class="hms-application-submit-button"');
        
        $form->addButton('quit', _('I Disagree'));
        $form->setExtra('quit', 'onclick="javascript:window.location.href='."'".'index.php?module=users&action=user&command=logout'."'".';"');

        $tpl = $form->getTemplate();

        $tpl['TERM'] = Term::toString($this->term);
        $tpl['CONTRACT'] = str_replace("\n", "<br />", file_get_contents('mod/hms/inc/contract.txt'));

        return PHPWS_Template::process($tpl, 'hms', 'student/applications/contract.tpl');
	}
}
?>
