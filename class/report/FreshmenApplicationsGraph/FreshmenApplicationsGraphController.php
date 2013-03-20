<?php

class FreshmenApplicationsGraphController extends ReportController implements iSyncReport, iSchedReport, iHtmlReportView {

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

?>