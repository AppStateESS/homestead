<?php

/*
 *
 * @author Jeremy Booker
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class ImproperCheckoutsController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iCsvReportView {

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


