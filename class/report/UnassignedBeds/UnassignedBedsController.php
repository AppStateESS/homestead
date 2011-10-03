<?php

/**
 * Controller for the UnassignedBeds report.
 * 
 * @author Jeremy Booker
 * @package HMS
 */

class UnassignedBedsController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iHtmlReportView, iPdfReportView, iCsvReportView {

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

?>