<?php

namespace Homestead\report\CancelledAppsList;

/**
 * Cancelled Applications List Controller
 *
 * @author Jeremy Booker
 * @package HMS
 */

class CancelledAppsListController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iCsvReportView {

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
