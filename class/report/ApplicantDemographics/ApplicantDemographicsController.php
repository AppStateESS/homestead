<?php

class ApplicantDemographicsController extends ReportController implements iHtmlReportView, iPdfReportView {

    const allowSyncExec = true;
    const allowAsyncExec = true;
    const allowScheduledExec = true;

    public function setParamsFromContext(CommandContext $context)
    {
        $this->report->setTerm(Term::getSelectedTerm());
    }
}

?>