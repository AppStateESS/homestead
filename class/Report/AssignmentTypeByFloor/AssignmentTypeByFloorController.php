<?php

namespace Homestead\Report\AssignmentTypeByFloor;

use \Homestead\ReportController;
use \Homestead\iSyncReport;
use \Homestead\iAsyncReport;
use \Homestead\iSchedReport;
use \Homestead\iHtmlReportView;
use \Homestead\iPdfReportView;

class AssignmentTypeByFloorController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iHtmlReportView, iPdfReportView {

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
