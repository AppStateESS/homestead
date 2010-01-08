<?php

PHPWS_Core::initModClass('hms', 'View.php');

class TermsConditionsAdminView extends View
{
	private $term;
	
	public function __construct($term)
	{
		if(is_a($term, 'Term')) {
			$this->term = $term;
		} else {
			$this->term = new Term($term);
		}
	}
	
	public function show()
	{
		$vars = array();
		
		$pdf = $this->term->getPdfTerms();
		$txt = $this->term->getTxtTerms();
		
		if(is_null($pdf) || empty($pdf)) {
			$tpl['PDF'] = 'No PDF has been uploaded.';
		} else {
			// TODO
		}
		
		if(is_null($txt) || empty($txt)) {
			$tpl['TXT'] = 'No Plain Text has been uploaded.';
		} else {
			// TODO
		}
		
		$cmd = CommandFactory::getCommand('ShowUploadTermsConditions');
		$cmd->setTerm($this->term->term);
		$cmd->setType('pdf');
		$tpl['PDF_UPLOAD'] = $cmd->getLink('Upload PDF', NULL, 'popup-link', 'Upload PDF');

		$cmd->setType('txt');
		$tpl['TXT_UPLOAD'] = $cmd->getLink('Upload Plain Text', NULL, 'popup-link', 'Upload Plain Text');
		
		$vars=array('LINK_SELECTOR' => 'a.popup-link');
		javascript('linkPopup');
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/TermsConditionsAdminView.tpl');
	}
}

?>