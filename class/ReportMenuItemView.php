<?php

class ReportMenuItemView extends View {

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

        $tpl['NAME'] = $this->report->getFriendlyName();
        
        if(is_null($this->report->getId())){
            $tpl['LAST_EXEC'] = 'never';
        }else{
            $tpl['LAST_EXEC']    = HMS_Util::relativeTime($this->report->getCompletedTimestamp());
        }
        
        $detailsCmd = CommandFactory::getCommand('ShowReportDetail');
        $detailsCmd->setReportClass($this->reportClassName);
        $tpl['DETAILS_LINK'] = $detailsCmd->getLink('details');
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/reportMenuItem.tpl');
    }
}

?>