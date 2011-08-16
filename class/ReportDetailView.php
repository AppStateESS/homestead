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
        
        if($this->reportCtrl->allowSyncExec()){
            $runNowCmd = $this->reportCtrl->getSyncExecCmd();
            $tpl['RUN_NOW'] = $runNowCmd->getLink('Run now');
        }else{
            $tpl['RUN_NOW'] = '(run now not allowed)';
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/reportDetailView.tpl');
    }
}

?>