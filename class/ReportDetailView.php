<?php

PHPWS_Core::initModClass('hms', 'ReportHistoryPager.php');
PHPWS_Core::initModClass('hms', 'ReportSchedulePager.php');

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
    private $report;
    
    /**
     * Constructor
     * 
     * @param ReportController $reportCtrl
     */
    public function __construct(ReportController $reportCtrl)
    {
        $this->reportCtrl = $reportCtrl;
        
        $this->reportCtrl->loadLastExec();
        $this->report = $reportCtrl->getReport();
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
        
        if(is_null($this->report->getId())){
            $tpl['NEVER_RUN'] = ""; // dummy tag
        }else{
            $viewCmd = $this->report->getDefaultOutputViewCmd();
            $tpl['LAST_RUN_RELATIVE'] = $viewCmd->getLink($this->report->getRelativeLastRun());
            $tpl['LAST_RUN_USER'] = $this->report->getLastRunUser();
        }
        
        $resultsPager = new ReportHistoryPager($this->reportCtrl);
        $tpl['RESULTS_PAGER'] = $resultsPager->get();
        
        $schedulePager = new ReportSchedulePager($this->reportCtrl);
        $tpl['SCHEDULE_PAGER'] = $schedulePager->get();
        
        if($this->reportCtrl instanceof iSyncReport){
            $runNowCmd = $this->reportCtrl->getSyncExecCmd();
            $tpl['RUN_NOW'] = $runNowCmd->getLink('Run now');
        }else{
            $tpl['RUN_NOW_DISABLED'] = ""; // dummy tag
        }
        
        if($this->reportCtrl instanceof iAsyncReport){
            $bgSetupView = $this->reportCtrl->getAsyncSetupView();
            $tpl['RUN_BACKGROUND'] = $bgSetupView->show();
        }else{
            $tpl['RUN_BACKGROUND_DISABLED'] = ""; // dummy tag
        }
        
        if($this->reportCtrl instanceof iSchedReport){
            $schedSetupView = $this->reportCtrl->getSchedSetupView();
            $tpl['RUN_SCHEDULE'] = $schedSetupView->show();
        }else{
            $tpl['RUN_SCHEDULE_DISABLED'] = ""; // dummy tag
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/reportDetailView.tpl');
    }
}

?>