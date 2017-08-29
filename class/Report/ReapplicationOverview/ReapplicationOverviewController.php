<?php

namespace Homestead\Report\ReapplicationOverview;

/**
 * Controller for the ReapplicationOverview report.
 *
 * @author Jeremy Booker
 * @package HMS
 */

class ReapplicationOverviewController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iHtmlReportView, iPdfReportView, iCsvReportView {

    public function setParams(Array $params)
    {
        $this->report->setTerm($params['term']);
    }

    public function getParams()
    {
        $params = array();

        $params['term'] = $this->report->getTerm();

        return $params;
    }

}
