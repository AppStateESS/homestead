<?php

namespace Homestead\Report\EarlyRelease;

use \Homestead\ReportController;
use \Homestead\iSyncReport;
use \Homestead\iAsyncReport;
use \Homestead\iSchedReport;
use \Homestead\iHtmlReportView;
use \Homestead\iPdfReportView;
use \Homestead\iCsvReportView;

  class EarlyReleaseController extends ReportController
        implements iSyncReport, iAsyncReport, iSchedReport, iHtmlReportView,
                    iPdfReportView, iCsvReportView
  {

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
