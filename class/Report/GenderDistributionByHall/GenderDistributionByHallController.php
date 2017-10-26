<?php

namespace Homestead\Report\GenderDistributionByHall;

use \Homestead\ReportController;
use \Homestead\iSyncReport;
use \Homestead\iHtmlReportView;
use \Homestead\iPdfReportView;
use \Homestead\iCsvReportView;

/**
 *
 * @author Jeremy Booker
 * @package HMS
 */

class GenderDistributionByHallController extends ReportController implements iSyncReport, iHtmlReportView, iPdfReportView, iCsvReportView {

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
