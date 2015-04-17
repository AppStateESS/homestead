<?php

PHPWS_Core::initModClass('hms', 'View.php');

class TermsConditionsAdminView extends hms\View {
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

        try{
            $pdf = $this->term->getPdfTerms();
        }catch(InvalidConfigurationException $e){
            $pdf = null;
            // It's ok to throw these away here
        }
        
        try{
            $txt = $this->term->getTxtTerms();
        }catch(InvalidConfigurationException $e){
            $txt = null;
            // It's ok to throw these away here
        }
        
        if(!isset($pdf) || is_null($pdf) || empty($pdf)) {
            $tpl['PDF'] = 'No PDF has been uploaded.';
        } else {
            $tpl['PDF'] = '<a href="'.$pdf.'">View PDF</a>';
        }

        if(!isset($txt) || is_null($txt) || empty($txt)) {
            $tpl['TXT'] = 'No Plain Text has been uploaded.';
        } else {
            $tpl['TXT'] = '<a href="'.$txt.'">View Plain Text</a>';
        }

        $cmd = CommandFactory::getCommand('ShowUploadTermsConditions');
        $cmd->setTerm($this->term->term);
        $cmd->setType('pdf');
        $tpl['PDF_UPLOAD'] = $cmd->getLink('Upload PDF', NULL, 'popup-link', 'Upload PDF');

        $cmd->setType('txt');
        $tpl['TXT_UPLOAD'] = $cmd->getLink('Upload Plain Text', NULL, 'popup-link', 'Upload Plain Text');

        $vars=array('LINK_SELECT' => 'a.popup-link');
        javascript('modules/hms/linkPopup', $vars);

        return PHPWS_Template::process($tpl, 'hms', 'admin/TermsConditionsAdminView.tpl');
    }
}

?>
