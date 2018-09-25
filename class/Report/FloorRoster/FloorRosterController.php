<?php

namespace Homestead\Report\FloorRoster;

use \Homestead\ReportController;
use \Homestead\iSyncReport;
use \Homestead\iAsyncReport;
use \Homestead\iSchedReport;
use \Homestead\iPdfReportView;
use \Homestead\iHtmlReportView;
use \Homestead\Report\FloorRoster\FloorRosterPdfView;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class FloorRosterController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iHtmlReportView, iPdfReportView {

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

    public function getPdfView()
    {
        $floor = new FloorRosterPdfView($this->report);
        return $floor;
    }

}
