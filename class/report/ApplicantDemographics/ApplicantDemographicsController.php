<?php

class ApplicantDemographicsController extends ReportController implements iSyncReport, iAsyncReport, iHtmlReportView, iPdfReportView {

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