<?php

namespace Homestead\report\CancelledAppsByReason;

/**
 * Cancelled Application by Reason Controller
 *
 * @author Jeremy Booker
 * @package HMS
 */

class CancelledAppsByReasonController extends ReportController implements iSyncReport, iSchedReport, iHtmlReportView, iPdfReportView {

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
