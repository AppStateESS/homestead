<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
PHPWS_Core::initModClass('hms', 'WKPDF.php');
PHPWS_Core::initModClass('hms', 'report/FloorRoster/FloorRosterPdfView.php');

class FloorRosterController extends ReportController implements iSyncReport, iPdfReportView {

//put your code here
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

?>
