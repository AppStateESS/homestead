<?php

class TermsConditionsAdminView extends hms\View{

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


        // Over 18 template
        $existingTemplate = '';

        try {
        	$existingTemplate = $this->term->getDocusignTemplate();
        } catch (InvalidConfigurationException $e) {
        	NQ::simple('hms', hms\NotificationView::WARNING, 'No DocuSign template id has been set for students over 18.');
        }

        $form->addText('template', $existingTemplate);
        $form->addCssClass('template', 'form-control');


        // Under 18 template
        $under18Template = '';

        try{
        	$under18Template = $this->term->getDocusignUnder18Template();
        } catch (InvalidConfigurationException $e) {
            NQ::simple('hms', hms\NotificationView::WARNING, 'No DocuSign template id has been set for students under 18.');
        }

        $form->addText('under18_template', $under18Template);
        $form->addCssClass('under18_template', 'form-control');


        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/TermsConditionsAdminView.tpl');
    }
}
