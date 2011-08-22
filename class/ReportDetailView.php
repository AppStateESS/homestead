<?php

PHPWS_Core::initModClass('hms', 'ReportHistoryPager.php');

/**
 * ReportDetailView
 * 
 * View that shows the interface with the details of a report class.
 * 
 * @author jbooker
 * @package HMS
 */
class ReportDetailView extends View {
    
    private $reportCtrl; // ReportController for the report class requested
    
    /**
     * Constructor
     * 
     * @param ReportController $reportCtrl
     */
    public function __construct(ReportController $reportCtrl)
    {
        $this->reportCtrl = $reportCtrl;
    }
    
    /**
     * Shows the report detail interface
     * @return String HTML for the report detail interface
     */
    public function show()
    {
        $this->setTitle($this->reportCtrl->getFriendlyName() . ' Detail');
        $tpl = array();
        
        $tpl['NAME'] = $this->reportCtrl->getFriendlyName();
        
        $resultsPager = new ReportHistoryPager($this->reportCtrl);
        
        $tpl['RESULTS_PAGER'] = $resultsPager->get();
        
        if($this->reportCtrl instanceof iSyncReport){
            $runNowCmd = $this->reportCtrl->getSyncExecCmd();
            $tpl['RUN_NOW'] = $runNowCmd->getLink('Run now');
        }else{
            $tpl['RUN_NOW'] = '(run now not allowed)';
        }
        
        if($this->reportCtrl instanceof iAsyncReport){
            $bgSetupView = $this->reportCtrl->getAsyncSetupView();
            $tpl['RUN_BACKGROUND'] = $bgSetupView->show();
        }else{
            $tpl['RUN_BACKGROUND'] = '(background execution not allowed)';
        }
        
        if($this->reportCtrl instanceof iSchedReport){
            $runSchedCmd = $this->reportCtrl->getSchedExecCmd();
            $tpl['RUN_SCHEDULE'] = $runSchedCmd->getLink('Schedule');
        }else{
            $tpl['RUN_SCHEDULE'] = '(scheduled exection not allowed)';
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/reportDetailView.tpl');
    }
}

?>