<?php

namespace Homestead\report\EarlyRelease;

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
