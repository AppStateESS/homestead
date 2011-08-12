<?php

class PackageDeskController extends ReportController implements iCsvReportView{

    const allowSyncExec = true;
    const allowAsyncExec = true;
    const allowScheduledExec = true;

    public function setParamsFromContext(CommandContext $context)
    {
        $this->report->setTerm(Term::getSelectedTerm());
    }
}

?>