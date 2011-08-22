<?php

class ApplicantDemographicsSetupView extends ReportSetupView {

    protected function getDialogContents()
    {
        $form = new PHPWS_Form('report-setup-form');
        $form->addDropBox('term', Term::getTermsAssoc());
        
        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/reports/ApplicantDemographicsSetupView.tpl');
    }
}

?>