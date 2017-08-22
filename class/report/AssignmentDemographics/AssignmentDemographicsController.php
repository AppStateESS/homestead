<?php

namespace Homestead\report\AssignmentDemographics;

class AssignmentDemographicsController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iHtmlReportView, iPdfReportView {

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
