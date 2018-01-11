<?php

namespace Homestead\Report\AssignmentsRemoved;

use \Homestead\ReportController;
use \Homestead\iSyncReport;
use \Homestead\iAsyncReport;
use \Homestead\iSchedReport;
use \Homestead\iCsvReportView;

class AssignmentsRemovedController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iCsvReportView
{

    public function setParams(Array $params){
        $this->report->setTerm($params['term']);
    }

    public function getParams()
    {
        $params = array();

        $params['term'] = $this->report->getTerm();

        return $params;
    }
}
