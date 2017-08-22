<?php

namespace Homestead\report\FloorRoster;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
PHPWS_Core::initModClass('hms', 'WKPDF.php');

class FloorRosterController extends ReportController implements iSyncReport, iAsyncReport, iSchedReport, iPdfReportView {

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
