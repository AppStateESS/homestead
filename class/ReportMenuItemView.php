<?php

class ReportMenuItemView extends Homestead\View{

    private $report;
    private $reportClassName;

    public function __construct(Report $report, $reportClassName)
    {
        $this->report = $report;
        $this->reportClassName = $reportClassName;
    }

    public function show()
    {
        $tpl = array();

        $detailsCmd = CommandFactory::getCommand('ShowReportDetail');
        $detailsCmd->setReportClass($this->reportClassName);
        
        $tpl['NAME'] = $detailsCmd->getLink($this->report->getFriendlyName());
        
        if(is_null($this->report->getId())){
            $tpl['LAST_EXEC'] = 'never';
        }else{
            $viewCmd = $this->report->getDefaultOutputViewCmd();
            $tpl['LAST_EXEC']    = $viewCmd->getLink($this->report->getRelativeLastRun());
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/reportMenuItem.tpl');
    }
}

?>