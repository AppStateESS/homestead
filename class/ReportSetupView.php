<?php

/**
 * ReportSetupView
 * 
 * Parent class for report setup views. This class is
 * intended to be extended (one or more times) for each report.
 * 
 * @author jbooker
 */
abstract class ReportSetupView extends View {
    
    protected $report;
    
    public function __construct(Report $report){
        $this->report = $report;
    }
    
    protected abstract function getDialogContents();
    
    public function show()
    {
        javascript('jquery');
        javascript('jquery_ui');
        
        $tpl = array();
        
        $tpl['DIALOG_CONTENTS'] = $this->getDialogContents();
        $tpl['REPORT_NAME'] = $this->report->getFriendlyName();
        $tpl['REPORT_CLASS'] = $this->report->getClass();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/SetupDialog.tpl');
    }
}

?>