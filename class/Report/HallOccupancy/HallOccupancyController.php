<?php

namespace Homestead\Report\HallOccupancy;

use \Homestead\ReportController;
use \Homestead\iSyncReport;
use \Homestead\iAsyncReport;
use \Homestead\iSchedReport;
use \Homestead\iHtmlReportView;
use \Homestead\iPdfReportView;

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

/**
 * Description of HallOccupancyController
 *
 * @author matt
 */
class HallOccupancyController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iHtmlReportView, iPdfReportView {
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
