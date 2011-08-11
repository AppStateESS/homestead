<?php

class PackageDeskController extends ReportController {

    const allowSyncExec = true;
    const allowAsyncExec = true;
    const allowScheduledExec = true;

    public function setParamsFromContext(CommandContext $context)
    {
        $this->report->setTerm(Term::getSelectedTerm());
    }

    public function getHtmlView()
    {
        return null;
    }

    public function getPdfView()
    {
        return null;
    }

    public function getCsvView()
    {

    }
}

?>