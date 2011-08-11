<?php

PHPWS_Core::initModClass('hms', 'ReportHistoryPager.php');

class ReportDetailView extends View {
    
    private $reportCtrl;
    
    public function __construct(ReportController $reportCtrl)
    {
        $this->reportCtrl = $reportCtrl;
    }
    
    public function show()
    {
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