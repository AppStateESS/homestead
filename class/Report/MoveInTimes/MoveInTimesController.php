<?php

namespace Homestead\Report\MoveInTimes;

use \Homestead\ReportController;
use \Homestead\iSyncReport;
use \Homestead\iHtmlReportView;
use \Homestead\iPdfReportView;

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class MoveInTimesController extends ReportController implements iSyncReport, iHtmlReportView, iPdfReportView {

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
