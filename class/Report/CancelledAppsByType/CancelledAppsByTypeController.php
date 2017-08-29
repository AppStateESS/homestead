<?php

namespace Homestead\Report\CancelledAppsByType;

/**
 * Cancelled Application by Student Type Controller
 *
 * @author Jeremy Booker
 * @package HMS
 */

class CancelledAppsByTypeController extends ReportController implements iSyncReport, iSchedReport, iHtmlReportView, iPdfReportView {

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
