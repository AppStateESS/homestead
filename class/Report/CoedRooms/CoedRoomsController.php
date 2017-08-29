<?php

namespace Homestead\Report\CoedRooms;

/**
 *
 * @author John Felipe
 * @package HMS
 */

class CoedRoomsController extends ReportController implements iSyncReport, iHtmlReportView, iPdfReportView, iCsvReportView
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
