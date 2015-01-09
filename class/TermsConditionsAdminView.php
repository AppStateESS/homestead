<?php

PHPWS_Core::initModClass('hms', 'View.php');

class TermsConditionsAdminView extends homestead\View
{
    private $term;

    public function __construct(Term $term)
    {
        $this->term = $term;
    }

    public function show()
    {
        $vars = array();

        $submitCmd = CommandFactory::getCommand('SaveTermSettings');
        
        $form = new PHPWS_Form('docusign');
        $submitCmd->initForm($form);
        
        $existingTemplate = '';
        
        try {
        	$existingTemplate = $this->term->getDocusignTemplate();
        } catch (InvalidConfigurationException $e) {
        	// TODO: show a warning
        }
        
        $form->addText('template', $existingTemplate);
        $form->setSize('termplate', 33);
        $form->addSubmit('Save');
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/TermsConditionsAdminView.tpl');
    }
}

?>
