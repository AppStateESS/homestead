<?php

namespace Homestead;

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

        $detailsCmd = CommandFactory::getCommand('ShowReportDetail');
        $detailsCmd->setReportClass($this->reportClassName);

        $tpl['NAME'] = $this->report->getFriendlyName();
        $tpl['REPORT_DETAIL_URI'] = $detailsCmd->getURI();

        if(is_null($this->report->getId())){
            $tpl['LAST_EXEC'] = 'never';
        }else{
            //$viewCmd = $this->report->getDefaultOutputViewCmd();
            //$tpl['LAST_EXEC']    = $viewCmd->getLink($this->report->getRelativeLastRun());
            $tpl['LAST_EXEC']    = $this->report->getRelativeLastRun();
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/reportMenuItem.tpl');
    }
}
