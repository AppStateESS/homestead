<?php

class SingleGenderVsCoedPrefController extends ReportController implements iSyncReport, iHtmlReportView, iPdfReportView {
    
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